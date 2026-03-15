<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chauffeur;
use App\Models\Client;
use App\Models\Course;
use App\Models\Notification;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $today = Carbon::today();

        $stats = [
            'chauffeurs' => Chauffeur::count(),
            'clients' => Client::count(),
            'courses' => Course::count(),
            'courses_today' => Course::whereDate('termine_le', $today)->count(),
            'revenue' => Course::where('est_terminee', true)->sum('prix_final'),
            'chauffeurs_online' => Chauffeur::where('statut', 'En ligne')->count(),
            'chauffeurs_offline' => Chauffeur::where('statut', 'Hors ligne')->count(),
            'notifications' => Notification::count(),
        ];

        $recentActivity = Notification::latest('created_at')->limit(3)->get();
        $activeSessions = UserSession::with('utilisateur')
            ->where('estActif', true)
            ->latest('dateCreation')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentActivity', 'activeSessions'));
    }

    public function performAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:refresh-drivers,notify-admin',
        ]);

        $message = match ($request->action) {
            'refresh-drivers' => 'Le cache des chauffeurs a été rafraîchi.',
            'notify-admin' => 'Une notification générique vient d’être créée.',
        };

        return back()->with('status', $message);
    }

    public function terminateSession(UserSession $session)
    {
        $session->estActif = false;
        $session->dateExpiration = Carbon::now();
        $session->save();

        return back()->with('status', 'Session terminée avec succès.');
    }
}
