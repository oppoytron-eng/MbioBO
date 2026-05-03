<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ApiCourse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $showArchived = $request->boolean('archived');

        $query = User::where('role', 'client')
            ->withCount(['clientCourses as courses_count']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        // Pour l'archivage, on utilise est_actif au lieu de soft delete
        if ($showArchived) {
            $query->where('est_actif', false);
        }

        $clients = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        $stats = [
            'total' => User::where('role', 'client')->count(),
            'archived' => User::where('role', 'client')->where('est_actif', false)->count(),
            'disabled' => User::where('role', 'client')->where('est_actif', false)->count(),
            'active' => User::where('role', 'client')->where('est_actif', true)->count(),
        ];

        return view('admin.clients.index', compact('clients', 'search', 'showArchived', 'stats'));
    }

    public function show($clientId)
    {
        $client = User::where('role', 'client')
            ->withCount(['clientCourses as courses_count'])
            ->findOrFail($clientId);

        $history = ApiCourse::with('chauffeur')
            ->where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $courseStats = [
            'total' => $client->courses_count,
            'completed' => ApiCourse::where('client_id', $clientId)
                ->where('statut', 'terminee')->count(),
            'cancelled' => ApiCourse::where('client_id', $clientId)
                ->where('statut', 'annulee')->count(),
            'total_spent' => ApiCourse::where('client_id', $clientId)
                ->where('statut', 'terminee')->sum('prix_final'),
        ];

        return view('admin.clients.show', compact('client', 'history', 'courseStats'));
    }

    public function toggleActive(Request $request, $clientId)
    {
        $client = User::where('role', 'client')->findOrFail($clientId);

        $data = $request->validate([
            'action' => 'required|in:disable,enable',
        ]);

        $client->est_actif = $data['action'] === 'enable';
        $client->save();

        $message = $data['action'] === 'enable'
            ? 'Client réactivé avec succès.'
            : 'Client désactivé avec succès.';

        return back()->with('status', $message);
    }

    public function archive(Request $request, $clientId)
    {
        $client = User::where('role', 'client')->findOrFail($clientId);

        if ($request->boolean('force')) {
            $client->delete();

            return redirect()
                ->route('admin.clients.index')
                ->with('status', 'Client supprimé définitivement.');
        }

        $client->est_actif = false;
        $client->save();

        return redirect()
            ->route('admin.clients.index')
            ->with('status', 'Client archivé.');
    }

    public function restore($clientId)
    {
        $client = User::where('role', 'client')->findOrFail($clientId);

        $client->est_actif = true;
        $client->save();

        return redirect()
            ->route('admin.clients.show', $client->id)
            ->with('status', 'Client restauré.');
    }
}
