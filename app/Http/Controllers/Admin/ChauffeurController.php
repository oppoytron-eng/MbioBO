<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Chauffeur;
use App\Models\ApiCourse;
use App\Models\ApiDriverDocument;
use Illuminate\Http\Request;

class ChauffeurController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status');
        $showArchived = $request->boolean('archived');

        $query = User::where('role', 'chauffeur')
            ->with('chauffeurProfile')
            ->with('chauffeurStatus')
            ->withCount(['apiCourses as courses_count']);

        if ($statusFilter) {
            $query->where(function($q) use ($statusFilter) {
                $q->whereHas('chauffeurProfile', function($q) use ($statusFilter) {
                    $q->where('statut_operationnel', $statusFilter);
                })->orWhereHas('chauffeurStatus', function($q) use ($statusFilter) {
                    $q->where('statut_operationnel', $statusFilter);
                });
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        // Filtrer par archivés (est_actif = false)
        if ($showArchived) {
            $query->where('est_actif', false);
        }

        $chauffeurs = $query->orderByDesc('created_at')->paginate(12)->withQueryString();

        $stats = [
            'total' => User::where('role', 'chauffeur')->count(),
            'active' => User::where('role', 'chauffeur')
                ->whereHas('chauffeurProfile', function($q) {
                    $q->where('statut_operationnel', 'actif');
                })->count(),
            'suspended' => User::where('role', 'chauffeur')
                ->whereHas('chauffeurProfile', function($q) {
                    $q->where('statut_operationnel', 'suspendu');
                })->count(),
            'archived' => User::where('role', 'chauffeur')->where('est_actif', false)->count(),
            'online' => Chauffeur::where('statut', 'En ligne')->count(),
            'offline' => Chauffeur::where('statut', 'Hors ligne')->count(),
        ];

        return view('admin.chauffeurs.index', compact('chauffeurs', 'search', 'statusFilter', 'showArchived', 'stats'));
    }

    public function show($id)
    {
        $chauffeur = User::where('role', 'chauffeur')
            ->with('chauffeurProfile')
            ->with('chauffeurStatus')
            ->with('driverDocuments')
            ->withCount(['apiCourses as courses_count'])
            ->findOrFail($id);

        $history = ApiCourse::with('client')
            ->where('chauffeur_id', $id)
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $courseStats = [
            'total' => $chauffeur->courses_count,
            'completed' => ApiCourse::where('chauffeur_id', $id)
                ->where('statut', 'terminee')->count(),
            'revenue' => ApiCourse::where('chauffeur_id', $id)
                ->where('statut', 'terminee')->sum('prix_final'),
            'average_distance' => ApiCourse::where('chauffeur_id', $id)
                ->avg('distance_meters') / 1000, // Convert meters to km
        ];

        return view('admin.chauffeurs.show', compact('chauffeur', 'history', 'courseStats'));
    }

    public function toggleStatus(Request $request, $chauffeurId)
    {
        $data = $request->validate([
            'action' => 'required|in:activate,deactivate,suspend,online,offline',
        ]);

        $chauffeur = User::where('role', 'chauffeur')->findOrFail($chauffeurId);
        $profile = $chauffeur->chauffeurProfile;

        if (!$profile) {
            $profile = Chauffeur::create([
                'utilisateur_id' => $chauffeur->id,
                'statut' => 'Hors ligne',
                'statut_operationnel' => 'actif',
                'est_actif' => true,
            ]);
        }

        switch ($data['action']) {
            case 'activate':
                $chauffeur->est_actif = true;
                $profile->statut_operationnel = 'actif';
                break;
            case 'deactivate':
                $chauffeur->est_actif = false;
                $profile->statut_operationnel = 'inactif';
                break;
            case 'suspend':
                $chauffeur->est_actif = false;
                $profile->statut_operationnel = 'suspendu';
                break;
            case 'online':
                $profile->statut = 'En ligne';
                break;
            case 'offline':
                $profile->statut = 'Hors ligne';
                break;
        }

        $chauffeur->save();
        $profile->save();

        return back()->with('status', 'Statut du chauffeur mis à jour.');
    }

    public function archive($chauffeurId)
    {
        $chauffeur = User::where('role', 'chauffeur')->findOrFail($chauffeurId);
        
        // Désactiver le profil chauffeur si existant
        if ($chauffeur->chauffeurProfile) {
            $chauffeur->chauffeurProfile->delete();
        }
        
        // Désactiver l'utilisateur
        $chauffeur->est_actif = false;
        $chauffeur->save();

        return redirect()->route('admin.chauffeurs.index')->with('status', 'Chauffeur archivé.');
    }

    public function restore($chauffeurId)
    {
        $chauffeur = User::where('role', 'chauffeur')->findOrFail($chauffeurId);

        $chauffeur->est_actif = true;
        $chauffeur->save();
        
        if ($chauffeur->chauffeurProfile) {
            $chauffeur->chauffeurProfile->restore();
        }

        return redirect()->route('admin.chauffeurs.show', $chauffeur->id)->with('status', 'Chauffeur restauré.');
    }

    public function reviewDocument(Request $request, $chauffeurId, $documentId)
    {
        $data = $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string',
        ]);

        $document = ApiDriverDocument::where('chauffeur_id', $chauffeurId)->findOrFail($documentId);

        $document->status = $data['status'];
        $document->notes = $data['notes'] ?? null;
        $document->reviewed_at = now();
        $document->save();

        return back()->with('status', 'Document mis à jour.');
    }
}
