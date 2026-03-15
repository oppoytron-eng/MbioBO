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

        $countdownSeconds = (int) max($request->input('countdown_seconds', $course->countdown_seconds ?? 120), 30);
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

    public function mesNotifications(Request $request)
    {
        $notifications = ApiNotificationChauffeur::where('chauffeur_id', $request->user()->id)
            ->where('expire_le', '>', now())
            ->where('statut', 'en_attente')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($notifications);
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

        foreach ($this->requiredDocumentTypes as $type) {
            $query->whereHas('driverDocuments', function ($q) use ($type) {
                $q->where('type', $type)->where('status', 'approved');
            });
        }

        return $query->get();
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
