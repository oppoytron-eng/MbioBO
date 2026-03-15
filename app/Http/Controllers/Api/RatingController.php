<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiClientRating;
use App\Models\ApiCourse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function rateClient(Request $request, $courseId)
    {
        $course = ApiCourse::findOrFail($courseId);

        if ($course->chauffeur_id !== $request->user()->id) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
        ]);

        $rating = ApiClientRating::updateOrCreate(
            [
                'course_id' => $course->id,
                'driver_id' => $request->user()->id,
                'client_id' => $course->client_id,
            ],
            [
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Client noté',
            'rating' => $rating,
        ]);
    }

    public function clientRatings(Request $request, $clientId)
    {
        if ($request->user()->role !== 'admin' && $request->user()->id !== $clientId) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $ratings = ApiClientRating::where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'ratings' => $ratings,
            'count' => $ratings->count(),
            'average' => $ratings->avg('rating'),
        ]);
    }
}
