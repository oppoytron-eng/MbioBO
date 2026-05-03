<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiCourse;
use App\Models\ApiNotification;
use App\Models\ApiNotificationChauffeur;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private array $requiredDocumentTypes = ['permis', 'assurance', 'carte_grise'];

    public function notifierChauffeurs(Request $request, $course_id)
    {
        $course = ApiCourse::with('client')->findOrFail($course_id);

        $countdownSeconds = (int) max($request->input('countdown_seconds', $course->countdown_seconds ?? 300), 30);
        $expireAfterSeconds = (int) max($request->input('expire_after_seconds', $countdownSeconds), 30);
        $distanceMeters = (float) ($request->input('distance_meters') ?? $course->distance_meters ?? $this->calculateDistanceMeters(
            $course->depart_latitude,
            $course->depart_longitude,
            $course->arrivee_latitude,
            $course->arrivee_longitude,
        ));

        $payload = [
            'depart' => [
                'latitude' => $course->depart_latitude,
                'longitude' => $course->depart_longitude,
            ],
            'arrivee' => [
                'latitude' => $course->arrivee_latitude,
                'longitude' => $course->arrivee_longitude,
            ],
            'prix_estime' => $course->prix_estime,
            'metadata' => $course->metadata ?? [],
        ];

        $clientInfo = $course->client_snapshot ?? $this->buildClientPayload($course);

        $notification = ApiNotification::create([
            'course_id' => $course->id,
            'type' => 'nouvelle_course',
            'message' => $request->input('message', 'Nouvelle course disponible'),
            'payload' => $payload,
            'distance_meters' => $distanceMeters,
            'sound' => $request->input('sound'),
            'countdown_active' => false,
            'countdown_seconds' => $countdownSeconds,
            'client_info' => $clientInfo,
        ]);

        $chauffeurs = $this->eligibleChauffeurs();
        
        foreach ($chauffeurs as $chauffeur) {
            ApiNotificationChauffeur::create([
                'notification_id' => $notification->id,
                'chauffeur_id' => $chauffeur->id,
                'statut' => 'en_attente',
                'expire_le' => now()->addSeconds($expireAfterSeconds),
            ]);
        }

        return response()->json([
            'message' => 'Chauffeurs notifiés',
            'notification' => $notification,
            'nb_chauffeurs' => $chauffeurs->count(),
        ]);
    }

    // Authentifier pour les channels privés WebSocket
    public function authenticate(Request $request)
    {
        $data = $request->validate([
            'channel_name' => ['required', 'string'],
            'socket_id' => ['required', 'string'],
        ]);

        $user = $request->user();
        
        // Vérifier si l'utilisateur a le droit d'accéder à ce channel
        if (str_starts_with($data['channel_name'], 'private-course.')) {
            $courseId = str_replace('private-course.', '', $data['channel_name']);
            $course = \App\Models\ApiCourse::find($courseId);
            
            if (!$course || ($course->client_id !== $user->id && $course->chauffeur_id !== $user->id)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }
        
        if (str_starts_with($data['channel_name'], 'private-chauffeur.')) {
            $chauffeurId = str_replace('private-chauffeur.', '', $data['channel_name']);
            if ($user->id !== (int)$chauffeurId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        // Générer la signature d'authentification
        $authKey = env('PUSHER_APP_KEY');
        $authSecret = env('PUSHER_APP_SECRET');
        
        $authData = [
            'auth_key' => $authKey,
            'auth_secret' => $authSecret,
            'socket_id' => $data['socket_id'],
            'channel_name' => $data['channel_name'],
        ];

        $stringToSign = implode(':', [
            $authData['socket_id'],
            $authData['channel_name'],
        ]);

        $signature = hash_hmac('sha256', $stringToSign, $authSecret);

        return response()->json([
            'auth' => $signature,
            'channel_data' => [
                'user_id' => $user->id,
                'user_info' => [
                    'name' => $user->name,
                    'role' => $user->role,
                ],
            ],
        ]);
    }

    // Obtenir les notifications de l'utilisateur connecté
    public function mesNotifications(Request $request)
    {
        $user = $request->user();
        
        try {
            if ($user->role === 'chauffeur') {
                // Pour les chauffeurs : notifications de courses + notifications admin
                $courseNotifications = ApiNotificationChauffeur::where('chauffeur_id', $user->id)
                    ->where('expire_le', '>', now())
                    ->where('statut', 'en_attente')
                    ->with('notification') // ✅ CHARGER LA RELATION
                    ->orderByDesc('created_at')
                    ->get();

                // Récupérer les notifications admin globales et pour chauffeurs
                $adminNotifications = ApiNotification::whereIn('type', ['admin_broadcast', 'admin_notification'])
                    ->where(function($query) {
                        $query->whereNull('course_id')
                              ->orWhere('course_id', 0);
                    })
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get();

                return response()->json([
                    'course_notifications' => $courseNotifications,
                    'admin_notifications' => $adminNotifications
                ]);
            } elseif ($user->role === 'client') {
                // Pour les clients : uniquement les notifications admin
                $adminNotifications = ApiNotification::whereIn('type', ['admin_broadcast', 'admin_notification'])
                    ->where(function($query) {
                        $query->whereNull('course_id')
                              ->orWhere('course_id', 0);
                    })
                    ->orderByDesc('created_at')
                    ->limit(20)
                    ->get();

                return response()->json([
                    'admin_notifications' => $adminNotifications
                ]);
            }

            return response()->json([]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des notifications',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function historique(Request $request)
    {
        $notifications = ApiNotificationChauffeur::with('notification')
            ->where('chauffeur_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($notifications);
    }

    public function marquerVue(Request $request, $id)
    {
        $notif = ApiNotificationChauffeur::where('id', $id)
            ->where('chauffeur_id', $request->user()->id)
            ->firstOrFail();

        $notif->update(['statut' => 'vue']);

        return response()->json(['message' => 'Notification marquée comme vue']);
    }

    public function demarrerCompteARebours(Request $request, $id)
    {
        $notif = ApiNotificationChauffeur::where('id', $id)
            ->where('chauffeur_id', $request->user()->id)
            ->firstOrFail();

        $data = $request->validate([
            'countdown_seconds' => ['nullable', 'integer', 'min:5'],
            'expire_after_seconds' => ['nullable', 'integer', 'min:5'],
        ]);

        $countdownSeconds = (int) max($data['countdown_seconds'] ?? $notif->notification->countdown_seconds ?? 30, 5);
        $expireAfter = (int) max($data['expire_after_seconds'] ?? $countdownSeconds, 5);

        $notif->update([
            'countdown_active' => true,
            'countdown_started_at' => now(),
            'countdown_expired_at' => now()->addSeconds($expireAfter),
        ]);

        $notif->notification->update([
            'countdown_active' => true,
            'countdown_seconds' => $countdownSeconds,
        ]);

        return response()->json([
            'message' => 'Compte à rebours déclenché',
            'notification' => $notif->fresh(),
        ]);
    }

    private function eligibleChauffeurs()
    {
        $query = User::where('role', 'chauffeur')
            ->where('est_actif', true);

        // Vérifier si le chauffeur a des documents validés (si possible)
        foreach ($this->requiredDocumentTypes as $type) {
            $query->whereHas('driverDocuments', function ($q) use ($type) {
                $q->where('type', $type)->where('status', 'approved');
            });
        }

        // Alternative: si aucun document requis, prendre tous les chauffeurs actifs
        $chauffeurs = $query->get();
        
        // Si aucun chauffeur n'a de documents validés, prendre tous les chauffeurs actifs
        if ($chauffeurs->isEmpty()) {
            $chauffeurs = User::where('role', 'chauffeur')
                ->where('est_actif', true)
                ->get();
        }

        return $chauffeurs;
    }

    private function buildClientPayload(ApiCourse $course): array
    {
        $client = $course->client;

        if (! $client) {
            return [];
        }

        return [
            'name' => $client?->name,
            'email' => $client?->email,
            'telephone' => $client?->telephone,
        ];
    }

    private function calculateDistanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);

        $a = sin($latDelta / 2) ** 2 +
            cos($lat1Rad) * cos($lat2Rad) * sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
