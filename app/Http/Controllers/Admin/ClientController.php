<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
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

        $query = Client::with(['utilisateur'])
            ->withCount('courses');

        if ($search) {
            $query->whereHas('utilisateur', function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        if ($showArchived) {
            $query->onlyTrashed();
        }

        $clients = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        $stats = [
            'total' => Client::count(),
            'archived' => Client::onlyTrashed()->count(),
            'disabled' => Client::where('est_actif', false)->count(),
        ];

        return view('admin.clients.index', compact('clients', 'search', 'showArchived', 'stats'));
    }

    public function show($clientId)
    {
        $client = Client::withTrashed()
            ->with(['utilisateur'])
            ->withCount('courses')
            ->findOrFail($clientId);

        $history = $client->courses()
            ->with('chauffeur.utilisateur')
            ->orderByDesc('termine_le')
            ->limit(12)
            ->get();

        return view('admin.clients.show', compact('client', 'history'));
    }

    public function toggleActive(Request $request, $clientId)
    {
        $client = Client::withTrashed()->findOrFail($clientId);

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
        $client = Client::withTrashed()->findOrFail($clientId);

        if ($request->boolean('force') && $client->trashed()) {
            $client->forceDelete();

            return redirect()
                ->route('admin.clients.index')
                ->with('status', 'Client supprimé définitivement.');
        }

        if (! $client->trashed()) {
            $client->delete();

            return redirect()
                ->route('admin.clients.index')
                ->with('status', 'Client archivé.');
        }

        return back()->with('status', 'Le client est déjà archivé.');
    }

    public function restore($clientId)
    {
        $client = Client::withTrashed()->findOrFail($clientId);

        if (! $client->trashed()) {
            return back()->with('status', 'Le client est déjà actif.');
        }

        $client->restore();

        return redirect()
            ->route('admin.clients.show', $client->id)
            ->with('status', 'Client restauré.');
    }
}
