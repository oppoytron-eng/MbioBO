<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiCourse;
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

        return response()->json([
            'message' => 'Course créée avec succès',
            'course' => $course->fresh(),
        ], 201);
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
        $course = ApiCourse::with(['client', 'chauffeur', 'tracks'])->findOrFail($id);
        return response()->json($course);
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

        return response()->json([
            'message' => 'OTP validé',
            'course' => $course->fresh(),
        ]);
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
}
