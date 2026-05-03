<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiCourse;
use App\Models\ApiNotification;
use App\Models\ApiNotificationChauffeur;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function creer(Request $request)
    {
        $data = $request->validate([
            'depart_latitude' => ['required', 'numeric'],
            'depart_longitude' => ['required', 'numeric'],
            'arrivee_latitude' => ['required', 'numeric'],
            'arrivee_longitude' => ['required', 'numeric'],
            'prix_estime' => ['required', 'numeric'],
            'mode_paiement' => ['nullable', 'string'],
            'countdown_seconds' => ['nullable', 'integer', 'min:0'],
            'metadata' => ['nullable', 'array'],
        ]);

        $distance = $this->calculateDistanceMeters(
            $data['depart_latitude'],
            $data['depart_longitude'],
            $data['arrivee_latitude'],
            $data['arrivee_longitude'],
        );

        $course = ApiCourse::create([
            'client_id' => $request->user()->id,
            'chauffeur_id' => null,
            'depart_latitude' => $data['depart_latitude'],
            'depart_longitude' => $data['depart_longitude'],
            'arrivee_latitude' => $data['arrivee_latitude'],
            'arrivee_longitude' => $data['arrivee_longitude'],
            'prix_estime' => $data['prix_estime'],
            'mode_paiement' => $data['mode_paiement'] ?? null,
            'distance_meters' => $distance,
            'metadata' => $data['metadata'] ?? [],
            'countdown_seconds' => $data['countdown_seconds'] ?? null,
            'client_snapshot' => $this->buildClientSnapshot($request->user()),
            'ride_otp' => $this->generateRideOtp(),
            'otp_expires_at' => now()->addMinutes(20),
        ]);

        // ✅ NOTIFIER AUTOMATIQUEMENT LES CHAUFFEURS
        $this->notifierChauffeurs($course);

        return response()->json([
            'message' => 'Course créée avec succès',
            'course' => $course->fresh(),
        ], 201);
    }

    public function index(Request $request)
    {
        $courses = ApiCourse::orderByDesc('created_at')->get();
        return response()->json($courses);
    }

    public function mesCourses(Request $request)
    {
        $courses = ApiCourse::where('client_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($courses);
    }

    public function disponibles()
    {
        $courses = ApiCourse::where('statut', 'en_attente')->whereNull('chauffeur_id')->get();
        return response()->json($courses);
    }

    public function accepter(Request $request, $id)
    {
        if ($request->user()->role !== 'chauffeur') {
            return response()->json(['message' => 'Action réservée aux chauffeurs'], 403);
        }

        $course = ApiCourse::findOrFail($id);

        if ($course->statut !== 'en_attente' || $course->chauffeur_id !== null) {
            return response()->json(['message' => 'Course non disponible'], 400);
        }

        $course->update([
            'chauffeur_id' => $request->user()->id,
            'statut' => 'acceptee',
        ]);

        return response()->json(['message' => 'Course acceptée', 'course' => $course]);
    }

    public function demarrer(Request $request, $id)
    {
        $course = ApiCourse::findOrFail($id);

        if ($request->user()->role !== 'chauffeur' || $course->chauffeur_id !== $request->user()->id) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        if ($course->statut !== 'acceptee') {
            return response()->json(['message' => 'La course ne peut pas être démarrée'], 400);
        }
        if ($course->ride_otp && ! $course->otp_verified_at) {
            return response()->json(['message' => 'La course nécessite la validation de l\'OTP client'], 400);
        }

        $course->update(['statut' => 'en_cours']);

        return response()->json(['message' => 'Course démarrée', 'course' => $course]);
    }

    public function terminer(Request $request, $id)
    {
        $course = ApiCourse::findOrFail($id);

        if ($request->user()->role !== 'chauffeur' || $course->chauffeur_id !== $request->user()->id) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        if ($course->statut !== 'en_cours') {
            return response()->json(['message' => 'La course ne peut pas être terminée'], 400);
        }

        $course->update([
            'statut' => 'terminee',
            'prix_final' => $request->input('prix_final', $course->prix_estime),
            'est_paye' => false,
        ]);

        return response()->json(['message' => 'Course terminée', 'course' => $course]);
    }

    public function annuler(Request $request, $id)
    {
        $course = ApiCourse::findOrFail($id);

        if ($course->client_id !== $request->user()->id && $course->chauffeur_id !== $request->user()->id) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $course->update(['statut' => 'annulee']);

        return response()->json(['message' => 'Course annulée', 'course' => $course]);
    }

    public function details($id)
    {
        $course = ApiCourse::with(['client', 'chauffeur', 'chauffeurProfile', 'tracks'])->findOrFail($id);
        
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

    public function verifierOtp(Request $request, $id)
    {
        $course = ApiCourse::findOrFail($id);

        if ($request->user()->role !== 'chauffeur' || $course->chauffeur_id !== $request->user()->id) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        if ($course->otp_verified_at) {
            return response()->json(['message' => 'OTP déjà validé'], 400);
        }

        if ($course->otp_expires_at && now()->greaterThan($course->otp_expires_at)) {
            return response()->json(['message' => 'OTP expiré'], 400);
        }

        $data = $request->validate([
            'otp' => ['required', 'string', 'min:4', 'max:6'],
            'auto_finish' => ['nullable', 'boolean'],
            'prix_final' => ['nullable', 'numeric'],
            'countdown_seconds' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($data['otp'] !== $course->ride_otp) {
            return response()->json(['message' => 'OTP invalide'], 400);
        }

        $update = [
            'otp_verified_at' => now(),
            'countdown_started_at' => now(),
        ];

        if (isset($data['countdown_seconds'])) {
            $update['countdown_seconds'] = $data['countdown_seconds'];
        }

        if (($data['auto_finish'] ?? false) === true) {
            $update['statut'] = 'terminee';
            $update['prix_final'] = $data['prix_final'] ?? $course->prix_estime;
            $update['est_paye'] = false;
        } else {
            $update['statut'] = 'acceptee';
        }

        $course->update($update);

        // 🎉 DIFFUSER L'ÉVÉNEMENT OTP VALIDÉ
        broadcast(new \App\Events\CourseOtpValidated($course->id, $course->chauffeur_id));

        return response()->json([
            'message' => 'OTP validé',
            'course' => $course->fresh(),
        ]);
    }

    private function calculateDistanceMeters($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Rayon de la Terre en mètres

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function calculateDistanceKm($lat1, $lon1, $lat2, $lon2)
    {
        return $this->calculateDistanceMeters($lat1, $lon1, $lat2, $lon2) / 1000;
    }

    private function buildClientSnapshot($client): array
    {
        return [
            'name' => $client->name,
            'email' => $client->email,
            'telephone' => $client->telephone,
        ];
    }

    private function generateRideOtp(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    // ✅ NOTIFIER AUTOMATIQUEMENT LES CHAUFFEURS DANS UN RAYON DE 5KM
    private function notifierChauffeurs(ApiCourse $course)
        {
            $countdownSeconds   = $course->countdown_seconds ?? 300; // 5 minutes
            $expireAfterSeconds = $countdownSeconds;
            $rayonKm            = 5; // Rayon de 5km

            $payload = [
                'depart' => [
                    'latitude'  => $course->depart_latitude,
                    'longitude' => $course->depart_longitude,
                ],
                'arrivee' => [
                    'latitude'  => $course->arrivee_latitude,
                    'longitude' => $course->arrivee_longitude,
                ],
                'prix_estime' => $course->prix_estime,
                'metadata'    => $course->metadata ?? [],
            ];

            // ✅ CORRIGÉ : utiliser les champs directs de la table users
            $chauffeursActifs = User::where('role', 'chauffeur')
                ->where('est_actif', true)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get();

            // Filtrer les chauffeurs dans un rayon de 5km
            $chauffeursProches = $chauffeursActifs->filter(function ($chauffeur) use ($course, $rayonKm) {
                // ✅ CORRIGÉ : utiliser les champs directs de la table users
                if (!$chauffeur->latitude || !$chauffeur->longitude) {
                    return false;
                }

                $distance = $this->calculateDistanceKm(
                    $course->depart_latitude,
                    $course->depart_longitude,
                    $chauffeur->latitude,   // ✅ champ correct
                    $chauffeur->longitude    // ✅ champ correct
                );

                // Ajouter les infos de position et distance pour l'affichage
                $chauffeur->distance_km = round($distance, 2);

                return $distance <= $rayonKm;
            });

            // Créer la notification
            $notification = ApiNotification::create([
                'course_id'          => $course->id,
                'type'               => 'nouvelle_course',
                'message'            => 'Nouvelle course disponible',
                'payload'            => $payload,
                'distance_meters'    => $course->distance_meters,
                'sound'              => null,
                'countdown_active'   => false,
                'countdown_seconds'  => $countdownSeconds,
                'client_info'        => $course->client_snapshot,
            ]);

            // Notifier uniquement les chauffeurs dans le rayon
            foreach ($chauffeursProches as $chauffeur) {
                ApiNotificationChauffeur::create([
                    'notification_id' => $notification->id,
                    'chauffeur_id'    => $chauffeur->id,
                    'statut'          => 'en_attente',
                    'expire_le'       => now()->addSeconds($expireAfterSeconds),
                ]);
            }

            // 🚀 BROADCASTER LES ÉVÉNEMENTS WEBSOCKET

            // 1. Notifier le client avec les positions des chauffeurs disponibles
            broadcast(new \App\Events\ChauffeursDisponiblesUpdated($course->id, $chauffeursProches));

            // 2. Notifier les chauffeurs concernés par la nouvelle course
            if ($chauffeursProches->count() > 0) {
                broadcast(new \App\Events\CourseRequested($course, $chauffeursProches));
            }

            \Log::info("Course {$course->id} notifiée à {$chauffeursProches->count()} chauffeurs dans un rayon de {$rayonKm}km (sur {$chauffeursActifs->count()} chauffeurs en ligne)");
        }
}