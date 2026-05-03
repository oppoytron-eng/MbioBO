<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiCourse;
use App\Models\ApiNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Statistiques du dashboard depuis les tables API
     */
    public function dashboardStats()
    {
        $today = Carbon::today();
        
        // Comptage depuis les tables API réelles
        $stats = [
            'chauffeurs' => User::where('role', 'chauffeur')->count(),
            'clients' => User::where('role', 'client')->count(),
            'courses' => ApiCourse::count(),
            'courses_today' => ApiCourse::whereDate('created_at', $today)->count(),
            'revenue' => ApiCourse::where('statut', 'terminee')->sum('prix_final'),
            // Pour les statuts, on utilise une approche directe
            'chauffeurs_online' => Chauffeur::where('statut', 'En ligne')->count(),
            'chauffeurs_offline' => Chauffeur::where('statut', 'Hors ligne')->count(),
            'notifications' => ApiNotification::count(),
        ];

        return response()->json($stats);
    }

    /**
     * Courses récentes pour le dashboard
     */
    public function recentCourses()
    {
        $courses = ApiCourse::with(['client', 'chauffeur'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json($courses);
    }

    /**
     * Détails d'une course API
     */
    public function courseDetails($id)
    {
        $course = ApiCourse::with(['client', 'chauffeur', 'chauffeurProfile', 'tracks'])
            ->findOrFail($id);

        // Ajouter les coordonnées du chauffeur si disponibles
        $courseData = $course->toArray();
        if ($course->chauffeurProfile && $course->chauffeurProfile->lat_actuelle && $course->chauffeurProfile->lng_actuelle) {
            $courseData['chauffeur_position'] = [
                'latitude' => $course->chauffeurProfile->lat_actuelle,
                'longitude' => $course->chauffeurProfile->lng_actuelle,
            ];
        }

        return response()->json($courseData);
    }

    /**
     * Liste des courses avec filtres
     */
    public function courses(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        $perPage = $request->input('per_page', 12);

        $query = ApiCourse::with(['client', 'chauffeur', 'chauffeurProfile']);

        // Filtres par statut
        if ($filter === 'cancelled') {
            $query->where('statut', 'annulee');
        } elseif ($filter === 'completed') {
            $query->where('statut', 'terminee');
        } elseif ($filter === 'active') {
            $query->whereIn('statut', ['en_attente', 'acceptee', 'en_cours']);
        }

        // Recherche par client/chauffeur
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('client', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('chauffeur', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        $courses = $query->orderByDesc('created_at')->paginate($perPage);

        // Comptages pour les filtres
        $counts = [
            'total' => ApiCourse::count(),
            'active' => ApiCourse::whereIn('statut', ['en_attente', 'acceptee', 'en_cours'])->count(),
            'cancelled' => ApiCourse::where('statut', 'annulee')->count(),
            'completed' => ApiCourse::where('statut', 'terminee')->count(),
        ];

        return response()->json([
            'courses' => $courses,
            'counts' => $counts,
        ]);
    }

    /**
     * Liste des chauffeurs avec leurs statuts
     */
    public function chauffeurs(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        $perPage = $request->input('per_page', 12);

        $query = User::where('role', 'chauffeur');

        if ($filter === 'online') {
            $query->whereHas('chauffeurProfile', function($q) {
                $q->where('statut', 'En ligne');
            });
        } elseif ($filter === 'offline') {
            $query->whereHas('chauffeurProfile', function($q) {
                $q->where('statut', 'Hors ligne');
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        $chauffeurs = $query->with('chauffeurProfile')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json($chauffeurs);
    }

    /**
     * Liste des clients
     */
    public function clients(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 12);

        $query = User::where('role', 'client');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json($clients);
    }

    /**
     * Notifications récentes
     */
    public function recentNotifications()
    {
        $notifications = ApiNotification::with(['course', 'user'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json($notifications);
    }

    /**
     * Statistiques détaillées par période
     */
    public function detailedStats(Request $request)
    {
        $period = $request->input('period', 'week'); // week, month, year
        $today = Carbon::today();

        $startDate = match($period) {
            'week' => $today->copy()->subDays(7),
            'month' => $today->copy()->subMonth(),
            'year' => $today->copy()->subYear(),
            default => $today->copy()->subDays(7),
        };

        $stats = [
            'period' => $period,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $today->format('Y-m-d'),
            'courses_count' => ApiCourse::whereBetween('created_at', [$startDate, $today])->count(),
            'completed_courses' => ApiCourse::where('statut', 'terminee')
                ->whereBetween('created_at', [$startDate, $today])->count(),
            'revenue' => ApiCourse::where('statut', 'terminee')
                ->whereBetween('created_at', [$startDate, $today])->sum('prix_final'),
            'new_clients' => User::where('role', 'client')
                ->whereBetween('created_at', [$startDate, $today])->count(),
            'new_drivers' => User::where('role', 'chauffeur')
                ->whereBetween('created_at', [$startDate, $today])->count(),
        ];

        return response()->json($stats);
    }
}
