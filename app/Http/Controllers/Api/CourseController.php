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
        ]);

        $course = ApiCourse::create([
            'client_id' => $request->user()->id,
            'depart_latitude' => $data['depart_latitude'],
            'depart_longitude' => $data['depart_longitude'],
            'arrivee_latitude' => $data['arrivee_latitude'],
            'arrivee_longitude' => $data['arrivee_longitude'],
            'prix_estime' => $data['prix_estime'],
            'mode_paiement' => $data['mode_paiement'] ?? null,
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
        $course = ApiCourse::findOrFail($id);
        return response()->json($course);
    }
}
