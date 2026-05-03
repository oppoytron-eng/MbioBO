<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiCourse;
use App\Models\User;
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

        $query = ApiCourse::with(['client', 'chauffeur']);

        // Filtres par statut API
        if ($filter === 'cancelled') {
            $query->where('statut', 'annulee');
        } elseif ($filter === 'completed') {
            $query->where('statut', 'terminee');
        } elseif ($filter === 'active') {
            $query->whereIn('statut', ['en_attente', 'acceptee', 'en_cours']);
        }

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

        $courses = $query->orderByDesc('created_at')->paginate(12)->withQueryString();
        $counts = [
            'total' => ApiCourse::count(),
            'active' => ApiCourse::whereIn('statut', ['en_attente', 'acceptee', 'en_cours'])->count(),
            'cancelled' => ApiCourse::where('statut', 'annulee')->count(),
            'completed' => ApiCourse::where('statut', 'terminee')->count(),
        ];

        return view('admin.courses.index', compact('courses', 'search', 'filter', 'counts'));
    }

    public function show($id)
    {
        $course = ApiCourse::with(['client', 'chauffeur', 'chauffeurProfile', 'tracks'])->findOrFail($id);

        $history = ApiCourse::with('chauffeur')
            ->where('client_id', $course->client_id)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return view('admin.courses.show', compact('course', 'history'));
    }

    public function cancel($id)
    {
        $course = ApiCourse::findOrFail($id);
        
        if ($course->statut !== 'annulee') {
            $course->update(['statut' => 'annulee']);
        }

        return back()->with('status', 'Course annulée.');
    }

    public function complete($id)
    {
        $course = ApiCourse::findOrFail($id);
        
        if ($course->statut !== 'terminee') {
            $course->update([
                'statut' => 'terminee',
                'prix_final' => $course->prix_estime,
            ]);
        }

        return back()->with('status', 'Course marquée comme terminée.');
    }
}
