<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    private array $requiredDocumentTypes = ['permis', 'assurance', 'carte_grise'];

    public function driversNeedingValidation(Request $request)
    {
        $this->assertAdmin($request->user());

        $drivers = User::where('role', 'chauffeur')
            ->with('driverDocuments')
            ->get()
            ->map(function (User $driver) {
                return [
                    'driver' => $driver,
                    'missing_documents' => $this->missingDocumentTypes($driver),
                    'documents' => $driver->driverDocuments,
                ];
            })
            ->filter(fn ($item) => count($item['missing_documents']) > 0)
            ->values();

        return response()->json($drivers);
    }

    private function missingDocumentTypes(User $driver): array
    {
        $approvedTypes = $driver->driverDocuments
            ->where('status', 'approved')
            ->pluck('type')
            ->map(fn ($type) => strtolower($type))
            ->unique()
            ->toArray();

        return array_values(array_diff($this->requiredDocumentTypes, $approvedTypes));
    }

    private function assertAdmin(User $user): void
    {
        if ($user->role !== 'admin') {
            abort(403, 'Action réservée aux administrateurs');
        }
    }
}
