<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiDriverDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'chauffeur') {
            return response()->json(['message' => 'Action réservée aux chauffeurs'], 403);
        }

        return response()->json(
            $request->user()
                ->driverDocuments()
                ->orderByDesc('uploaded_at')
                ->get()
        );
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'chauffeur') {
            return response()->json(['message' => 'Action réservée aux chauffeurs'], 403);
        }

        $data = $request->validate([
            'type' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $path = $request->file('document')->store('documents', 'public');

        $document = ApiDriverDocument::create([
            'chauffeur_id' => $request->user()->id,
            'type' => strtolower($data['type']),
            'notes' => $data['notes'] ?? null,
            'path' => $path,
        ]);

        return response()->json([
            'message' => 'Document téléversé',
            'document' => $document,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $document = ApiDriverDocument::findOrFail($id);

        if ($request->user()->id !== $document->chauffeur_id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        return response()->json($document);
    }

    public function review(Request $request, $id)
    {
        $this->assertAdmin($request->user());

        $document = ApiDriverDocument::findOrFail($id);

        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'notes' => ['nullable', 'string'],
        ]);

        $document->update([
            'status' => $data['status'],
            'notes' => $data['notes'] ?? $document->notes,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'message' => 'État du document mis à jour',
            'document' => $document,
        ]);
    }

    private function assertAdmin(User $user): void
    {
        if ($user->role !== 'admin') {
            abort(403, 'Action réservée aux administrateurs');
        }
    }
}
