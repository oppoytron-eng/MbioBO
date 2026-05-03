<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chauffeur;
use Illuminate\Http\Request;

/**
 * DriverProfileController
 *
 * Gère le profil opérationnel du chauffeur :
 *   GET  /api/driver/profile  → statut + position actuelle
 *   PATCH /api/driver/profile → mise à jour statut et/ou position GPS
 *
 * ✅ CORRIGÉ : utilise la relation chauffeurProfile (table chauffeurs,
 *    champ utilisateur_id) pour lire/écrire lat_actuelle et lng_actuelle.
 *    L'ancien code cherchait $user->latitude qui n'existe pas dans users.
 */
class DriverProfileController extends Controller
{
    /**
     * GET /api/driver/profile
     * Retourne le statut et la position actuelle du chauffeur connecté.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // Récupérer le profil chauffeur via la relation (table chauffeurs)
        $chauffeur = $user->chauffeurProfile;

        if (!$chauffeur) {
            return response()->json([
                'message' => 'Profil chauffeur introuvable',
            ], 404);
        }

        return response()->json([
            'chauffeur' => [
                'id'                   => $chauffeur->id,
                'statut'               => $chauffeur->statut,             // En ligne / Hors ligne / En course
                'lat_actuelle'         => $chauffeur->lat_actuelle,
                'lng_actuelle'         => $chauffeur->lng_actuelle,
                'statut_operationnel'  => $chauffeur->statut_operationnel,
                'est_actif'            => $chauffeur->est_actif,
            ],
        ]);
    }

    /**
     * PATCH /api/driver/profile
     *
     * Met à jour le statut et/ou la position GPS du chauffeur.
     *
     * Body (tous optionnels) :
     *   statut       : 'En ligne' | 'Hors ligne' | 'En course'
     *   lat_actuelle : float
     *   lng_actuelle : float
     *
     * Logique :
     *   - Passer à 'Hors ligne' désactive est_actif
     *   - Passer à 'En ligne' active est_actif + sauvegarde la position
     *   - Passer à 'En course' est_actif reste true (géré automatiquement
     *     par le CourseController lors de l'acceptation d'une course)
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'statut'       => ['nullable', 'in:En ligne,Hors ligne,En course'],
            'lat_actuelle' => ['nullable', 'numeric'],
            'lng_actuelle' => ['nullable', 'numeric'],
        ]);

        // ✅ Utiliser chauffeurProfile (relation User → Chauffeur via utilisateur_id)
        $chauffeur = $user->chauffeurProfile;

        if (!$chauffeur) {
            // Créer le profil si inexistant (cas rare — normalement créé à l'inscription)
            $chauffeur = Chauffeur::create([
                'utilisateur_id' => $user->id,
                'statut'         => $data['statut'] ?? 'Hors ligne',
                'lat_actuelle'   => $data['lat_actuelle'] ?? null,
                'lng_actuelle'   => $data['lng_actuelle'] ?? null,
                'est_actif'      => false,
            ]);
        } else {
            // Mettre à jour les champs fournis
            if (isset($data['statut'])) {
                $chauffeur->statut   = $data['statut'];
                // Sync est_actif avec le statut
                $chauffeur->est_actif = ($data['statut'] !== 'Hors ligne');
            }

            if (isset($data['lat_actuelle'])) {
                $chauffeur->lat_actuelle = $data['lat_actuelle'];
            }

            if (isset($data['lng_actuelle'])) {
                $chauffeur->lng_actuelle = $data['lng_actuelle'];
            }

            $chauffeur->save();
        }

        return response()->json([
            'message'   => 'Profil mis à jour',
            'chauffeur' => [
                'statut'       => $chauffeur->statut,
                'lat_actuelle' => $chauffeur->lat_actuelle,
                'lng_actuelle' => $chauffeur->lng_actuelle,
                'est_actif'    => $chauffeur->est_actif,
            ],
        ]);
    }
}
