<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');

        $query = Course::with(['client.utilisateur', 'chauffeur.utilisateur']);

        if ($filter === 'cancelled') {
            $query->where('est_annule', true);
        } elseif ($filter === 'completed') {
            $query->where('est_terminee', true);
        } elseif ($filter === 'active') {
            $query->where('est_annule', false)->where('est_terminee', false);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('client.utilisateur', function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%{$search}%")
                        ->orWhere('prenom', 'like', "%{$search}%");
                })->orWhereHas('chauffeur.utilisateur', function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%{$search}%")
                        ->orWhere('prenom', 'like', "%{$search}%");
                });
            });
        }

        $courses = $query->orderByDesc('termine_le')->paginate(12)->withQueryString();
        $counts = [
            'total' => Course::count(),
            'active' => Course::where('est_annule', false)->where('est_terminee', false)->count(),
            'cancelled' => Course::where('est_annule', true)->count(),
            'completed' => Course::where('est_terminee', true)->count(),
        ];

        return view('admin.courses.index', compact('courses', 'search', 'filter', 'counts'));
    }

    public function show(Course $course)
    {
        $course->load(['client.utilisateur', 'chauffeur.utilisateur', 'positions']);

        $history = Course::with('chauffeur.utilisateur')
            ->where('client_id', $course->client_id)
            ->orderByDesc('termine_le')
            ->limit(6)
            ->get();

        return view('admin.courses.show', compact('course', 'history'));
    }

    public function cancel(Course $course)
    {
        if (! $course->est_annule) {
            $course->update([
                'est_annule' => true,
                'est_terminee' => false,
            ]);
        }

        return back()->with('status', 'Course annulée.');
    }

    public function complete(Course $course)
    {
        if (! $course->est_terminee) {
            $course->update([
                'est_terminee' => true,
                'est_annule' => false,
                'termine_le' => now(),
            ]);
        }

        return back()->with('status', 'Course marquée comme terminée.');
    }
}
