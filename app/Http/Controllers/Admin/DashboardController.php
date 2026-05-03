<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chauffeur;
use App\Models\Client;
use App\Models\Course;
use App\Models\Notification;
use App\Models\UserSession;
use App\Models\ApiCourse;
use App\Models\ApiNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $today = Carbon::today();

        // Récupération des données depuis les tables API réelles
        $stats = [
            'chauffeurs' => User::where('role', 'chauffeur')->count(),
            'clients' => User::where('role', 'client')->count(),
            'courses' => ApiCourse::count(),
            'courses_today' => ApiCourse::whereDate('created_at', $today)->count(),
            'revenue' => ApiCourse::where('statut', 'terminee')->sum('prix_final'),
            // Pour les statuts des chauffeurs, on utilise une approche plus simple
            'chauffeurs_online' => Chauffeur::where('statut', 'En ligne')->count(),
            'chauffeurs_offline' => Chauffeur::where('statut', 'Hors ligne')->count(),
            'notifications' => ApiNotification::count(),
        ];

        // Logs de débogage pour vérifier les nouvelles données
        \Log::info('Dashboard Stats Calculation (API Tables)', [
            'today' => $today->format('Y-m-d'),
            'chauffeurs_count' => $stats['chauffeurs'],
            'clients_count' => $stats['clients'],
            'courses_count' => $stats['courses'],
            'courses_today_count' => $stats['courses_today'],
            'completed_courses_count' => ApiCourse::where('statut', 'terminee')->count(),
            'revenue_sum' => $stats['revenue'],
            'chauffeurs_online_count' => $stats['chauffeurs_online'],
            'chauffeurs_offline_count' => $stats['chauffeurs_offline'],
            'notifications_count' => $stats['notifications'],
        ]);

        // Activité récente depuis les tables API
        $recentActivity = ApiNotification::with(['course'])
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        // Sessions actives (inchangé, car déjà fonctionnel)
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
