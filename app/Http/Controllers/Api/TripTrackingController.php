<?php

namespace App\Http\Controllers\Api;

use App\Events\DriverLocationUpdated;
use App\Http\Controllers\Controller;
use App\Models\ApiCourse;
use App\Models\ApiCourseTrack;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TripTrackingController extends Controller
{
    public function store(Request $request, $courseId)
    {
        $course = ApiCourse::findOrFail($courseId);

        if ($course->chauffeur_id !== $request->user()->id) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        if (! in_array($course->statut, ['acceptee', 'en_cours', 'terminee'], true)) {
            return response()->json(['message' => 'La course n\'est pas active'], 400);
        }

        $data = $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'latitude_accuracy' => ['nullable', 'numeric'],
            'longitude_accuracy' => ['nullable', 'numeric'],
            'bearing' => ['nullable', 'numeric'],
            'speed' => ['nullable', 'numeric'],
            'recorded_at' => ['nullable', 'date'],
            'meta' => ['nullable', 'array'],
        ]);

        $track = ApiCourseTrack::create([
            'course_id' => $course->id,
            'chauffeur_id' => $request->user()->id,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'latitude_accuracy' => $data['latitude_accuracy'] ?? null,
            'longitude_accuracy' => $data['longitude_accuracy'] ?? null,
            'bearing' => $data['bearing'] ?? null,
            'speed' => $data['speed'] ?? null,
            'recorded_at' => isset($data['recorded_at']) ? Carbon::parse($data['recorded_at']) : now(),
            'meta' => $data['meta'] ?? [],
        ]);

        // Émettre l'événement WebSocket pour le suivi en temps réel
        broadcast(new DriverLocationUpdated($track))->toOthers();

        return response()->json(['message' => 'Position enregistrée', 'track' => $track]);
    }

    public function index(Request $request, $courseId)
    {
        $course = ApiCourse::findOrFail($courseId);
        if ($request->user()->id !== $course->client_id && $request->user()->id !== $course->chauffeur_id) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        return response()->json(
            $course->tracks()->orderBy('recorded_at')->get()
        );
    }

    public function latest(Request $request, $courseId)
    {
        $course = ApiCourse::with(['chauffeurProfile'])->findOrFail($courseId);
        if ($request->user()->id !== $course->client_id && $request->user()->id !== $course->chauffeur_id) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        // D'abord essayer de récupérer un track existant
        $track = $course->tracks()->orderByDesc('recorded_at')->first();
        
        // Si aucun track mais que le chauffeur a une position, utiliser celle-ci
        if (!$track && $course->chauffeurProfile && $course->chauffeurProfile->lat_actuelle && $course->chauffeurProfile->lng_actuelle) {
            $track = (object)[
                'id' => null,
                'course_id' => $course->id,
                'chauffeur_id' => $course->chauffeur_id,
                'latitude' => $course->chauffeurProfile->lat_actuelle,
                'longitude' => $course->chauffeurProfile->lng_actuelle,
                'bearing' => null,
                'speed' => null,
                'recorded_at' => now(),
            ];
        }

        return response()->json(['track' => $track]);
    }
}
