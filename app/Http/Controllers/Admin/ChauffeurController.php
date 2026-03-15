<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chauffeur;
use App\Models\ChauffeurDocument;
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
        $showArchived = $request->boolean('archived');
        $statusFilter = $request->input('status');

        $query = Chauffeur::with('utilisateur')->withCount('courses');

        if ($showArchived) {
            $query->onlyTrashed();
        } else {
            $query->whereNull('deleted_at');
        }

        if ($statusFilter) {
            $query->where('statut_operationnel', $statusFilter);
        }

        if ($search) {
            $query->whereHas('utilisateur', function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        $chauffeurs = $query->orderByDesc('created_at')->paginate(12)->withQueryString();

        $stats = [
            'total' => Chauffeur::count(),
            'active' => Chauffeur::where('statut_operationnel', 'actif')->count(),
            'suspended' => Chauffeur::where('statut_operationnel', 'suspendu')->count(),
            'archived' => Chauffeur::onlyTrashed()->count(),
        ];

        return view('admin.chauffeurs.index', compact('chauffeurs', 'search', 'showArchived', 'statusFilter', 'stats'));
    }

    public function show($chauffeurId)
    {
        $chauffeur = Chauffeur::withTrashed()
            ->with(['utilisateur', 'documents'])
            ->withCount('courses')
            ->findOrFail($chauffeurId);

        $history = $chauffeur->courses()
            ->with('client.utilisateur')
            ->orderByDesc('termine_le')
            ->limit(12)
            ->get();

        $courseStats = [
            'total' => $chauffeur->courses_count,
            'completed' => $chauffeur->courses()->whereNotNull('termine_le')->count(),
            'revenue' => $chauffeur->courses()->sum('prix_final'),
            'average_distance' => $chauffeur->courses()->avg('distance_km'),
        ];

        return view('admin.chauffeurs.show', compact('chauffeur', 'history', 'courseStats'));
    }

    public function toggleStatus(Request $request, $chauffeurId)
    {
        $data = $request->validate([
            'action' => 'required|in:activate,deactivate,suspend',
        ]);

        $chauffeur = Chauffeur::withTrashed()->findOrFail($chauffeurId);

        switch ($data['action']) {
            case 'activate':
                $chauffeur->est_actif = true;
                $chauffeur->statut_operationnel = 'actif';
                break;
            case 'deactivate':
                $chauffeur->est_actif = false;
                $chauffeur->statut_operationnel = 'inactif';
                break;
            default:
                $chauffeur->est_actif = false;
                $chauffeur->statut_operationnel = 'suspendu';
                break;
        }

        $chauffeur->save();

        return back()->with('status', 'Statut du chauffeur mis à jour.');
    }

    public function archive($chauffeurId)
    {
        $chauffeur = Chauffeur::withTrashed()->findOrFail($chauffeurId);

        if ($chauffeur->trashed()) {
            $chauffeur->forceDelete();

            return redirect()->route('admin.chauffeurs.index')->with('status', 'Chauffeur supprimé définitivement.');
        }

        $chauffeur->delete();

        return redirect()->route('admin.chauffeurs.index')->with('status', 'Chauffeur archivé.');
    }

    public function restore($chauffeurId)
    {
        $chauffeur = Chauffeur::withTrashed()->findOrFail($chauffeurId);

        if (! $chauffeur->trashed()) {
            return back()->with('status', 'Le chauffeur est déjà actif.');
        }

        $chauffeur->restore();
        $chauffeur->est_actif = true;
        $chauffeur->statut_operationnel = 'actif';
        $chauffeur->save();

        return redirect()->route('admin.chauffeurs.show', $chauffeur->id)->with('status', 'Chauffeur restauré.');
    }

    public function reviewDocument(Request $request, $chauffeurId, $documentId)
    {
        $data = $request->validate([
            'status' => 'required|in:validated,rejected',
            'notes' => 'nullable|string',
        ]);

        $document = ChauffeurDocument::where('chauffeur_id', $chauffeurId)->findOrFail($documentId);

        $document->status = $data['status'];
        $document->notes = $data['notes'] ?? null;
        $document->reviewed_at = now();
        $document->save();

        $chauffeur = $document->chauffeur;
        if ($chauffeur->documents()->where('status', '!=', 'validated')->count() === 0) {
            $chauffeur->etat_documents = 'validated';
        } elseif ($document->status === 'rejected') {
            $chauffeur->etat_documents = 'rejected';
        } else {
            $chauffeur->etat_documents = 'pending';
        }

        $chauffeur->save();

        return back()->with('status', 'Document mis à jour.');
    }
}
