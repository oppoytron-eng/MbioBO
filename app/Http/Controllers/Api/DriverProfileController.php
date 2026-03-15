<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chauffeur;
use Illuminate\Http\Request;

class DriverProfileController extends Controller
{
    public function show(Request $request)
    {
        $chauffeur = $request->user()->chauffeur;

        return response()->json([
            'chauffeur' => [
                'id' => $chauffeur->id,
                'statut' => $chauffeur->statut,
                'lat_actuelle' => $chauffeur->lat_actuelle,
                'lng_actuelle' => $chauffeur->lng_actuelle,
                'statut_operationnel' => $chauffeur->statut_operationnel,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $chauffeur = $request->user()->chauffeur;

        $data = $request->validate([
            'statut' => ['nullable', 'in:En ligne,Hors ligne,En course'],
            'lat_actuelle' => ['nullable', 'numeric'],
            'lng_actuelle' => ['nullable', 'numeric'],
        ]);

        if (isset($data['statut'])) {
            $chauffeur->statut = $data['statut'];
        }

        if (isset($data['lat_actuelle'])) {
            $chauffeur->lat_actuelle = $data['lat_actuelle'];
        }

        if (isset($data['lng_actuelle'])) {
            $chauffeur->lng_actuelle = $data['lng_actuelle'];
        }

        $chauffeur->save();

        return response()->json([
            'chauffeur' => [
                'statut' => $chauffeur->statut,
                'lat_actuelle' => $chauffeur->lat_actuelle,
                'lng_actuelle' => $chauffeur->lng_actuelle,
            ],
        ]);
    }
}
