<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chauffeur;
use App\Models\Utilisateur;
use App\Models\User;
use App\Models\ApiCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DriverAuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:utilisateurs,email'],
            'telephone' => ['required', 'string', 'max:20', 'unique:utilisateurs,telephone'],
            'mot_de_passe' => ['required', 'string', 'min:8', 'confirmed'],
            'numero_permis' => ['nullable', 'string', 'max:50', 'unique:chauffeurs,numero_permis'],
            'lat_actuelle' => ['required', 'numeric'],
            'lng_actuelle' => ['required', 'numeric'],
        ]);

        $utilisateurId = Str::uuid()->toString();

        $utilisateur = Utilisateur::create([
            'utilisateur_id' => $utilisateurId,
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'telephone' => $data['telephone'],
            'mot_de_passe' => Hash::make($data['mot_de_passe']),
            'role' => 'Chauffeur',
            'est_actif' => true,
            'statut' => 'deconnecté',
        ]);

        $chauffeur = Chauffeur::create([
            'id' => Str::uuid()->toString(),
            'utilisateur_id' => $utilisateurId,
            'numero_permis' => $data['numero_permis'] ?? 'TEMP_' . time(),
            'statut' => 'Hors ligne',
            'statut_operationnel' => 'actif',
            'note_moyenne' => 0,
            'nb_courses' => 0,
            'solde_total' => 0,
            'lat_actuelle' => $data['lat_actuelle'],
            'lng_actuelle' => $data['lng_actuelle'],
            'est_actif' => true,
            'etat_documents' => 'pending',
        ]);

        $token = $this->issueToken($utilisateur);

        return response()->json([
            'message' => 'Chauffeur enregistré avec succès.',
            'token' => $token,
            'chauffeur' => $chauffeur->load('utilisateur'),
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required_without:telephone', 'nullable', 'email'],
            'telephone' => ['required_without:email', 'nullable', 'string'],
            'mot_de_passe' => ['required', 'string'],
        ]);

        $utilisateur = $this->findDriver($data);

        if (! $utilisateur || ! Hash::check($data['mot_de_passe'], $utilisateur->mot_de_passe)) {
            throw ValidationException::withMessages(['mot_de_passe' => ['Identifiants invalides.']]);
        }

        $chauffeur = $utilisateur->chauffeur;

        if (! $chauffeur) {
            throw ValidationException::withMessages(['mot_de_passe' => ['Ce compte n’est pas rattaché à un chauffeur.']]);
        }

        $utilisateur->update(['statut' => 'libre']);
        $chauffeur->update(['statut' => 'En ligne', 'statut_operationnel' => 'actif']);

        $token = $this->issueToken($utilisateur);

        return response()->json([
            'token' => $token,
            'chauffeur' => $chauffeur->load('utilisateur'),
        ]);
    }

    public function logout(Request $request)
    {
        $utilisateur = $request->user();

        $utilisateur->currentAccessToken()?->delete();
        $utilisateur->chauffeur?->update(['statut' => 'Hors ligne']);
        $utilisateur->update(['statut' => 'deconnecté']);

        return response()->json(['message' => 'Déconnexion effectuée.']);
    }

    public function refresh(Request $request)
    {
        $utilisateur = $request->user();
        $utilisateur->currentAccessToken()?->delete();

        return response()->json(['token' => $this->issueToken($utilisateur)]);
    }

    public function session(Request $request)
    {
        $chauffeur = $request->user()->chauffeur;

        if (! $chauffeur) {
            return response()->json(['message' => 'Profil chauffeur introuvable.'], 404);
        }

        return response()->json([
            'chauffeur' => $chauffeur->load('utilisateur'),
            'token' => $request->bearerToken(),
        ]);
    }

    public function sendPasswordReset(Request $request)
    {
        $data = $request->validate([
            'email' => ['required_without:telephone', 'nullable', 'email'],
            'telephone' => ['required_without:email', 'nullable', 'string'],
        ]);

        $utilisateur = $this->findDriver($data);

        if (! $utilisateur) {
            return response()->json(['message' => 'Chauffeur introuvable.'], 404);
        }

        $otp = $utilisateur->generateOtp();

        // TODO: dispatch SMS/email with $otp; returning it for development convenience.

        return response()->json([
            'message' => 'OTP envoyé.',
            'otp' => $otp,
            'expires_in' => 600,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required_without:telephone', 'nullable', 'email'],
            'telephone' => ['required_without:email', 'nullable', 'string'],
            'otp' => ['required', 'string'],
        ]);

        $utilisateur = $this->findDriver($data);

        if (! $utilisateur || ! $utilisateur->otpIsValid($data['otp'])) {
            throw ValidationException::withMessages(['otp' => ['Code invalide ou expiré.']]);
        }

        $utilisateur->clearOtp();

        return response()->json(['message' => 'OTP validé.']);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required_without:telephone', 'nullable', 'email'],
            'telephone' => ['required_without:email', 'nullable', 'string'],
            'otp' => ['required', 'string'],
            'mot_de_passe' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $utilisateur = $this->findDriver($data);

        if (! $utilisateur || ! $utilisateur->otpIsValid($data['otp'])) {
            throw ValidationException::withMessages(['otp' => ['Code invalide ou expiré.']]);
        }

        $utilisateur->mot_de_passe = Hash::make($data['mot_de_passe']);
        $utilisateur->clearOtp();
        $utilisateur->save();

        return response()->json(['message' => 'Mot de passe mis à jour.']);
    }

    private function issueToken(Utilisateur $utilisateur): string
    {
        $utilisateur->tokens()->where('name', 'driver-app')->delete();

        return $utilisateur->createToken('driver-app')->plainTextToken;
    }

    // Récupérer les chauffeurs actifs avec leurs positions
    public function chauffeursActifs(Request $request)
    {
        $chauffeurs = User::where('role', 'chauffeur')
            ->where('est_actif', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($chauffeur) {
                return [
                    'id' => $chauffeur->id,
                    'name' => $chauffeur->name,
                    'telephone' => $chauffeur->telephone,
                    'latitude' => $chauffeur->latitude,
                    'longitude' => $chauffeur->longitude,
                    'est_actif' => $chauffeur->est_actif,
                    'distance_km' => 0, // Sera calculé côté client
                ];
            });

        return response()->json([
            'chauffeurs' => $chauffeurs,
            'total' => $chauffeurs->count()
        ]);
    }

    // Mettre à jour la position d'un chauffeur
    public function updatePosition(Request $request)
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $user = $request->user();
        
        if ($user->role !== 'chauffeur') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Mettre à jour la position du chauffeur
        $user->update([
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'est_actif' => true,
        ]);

        // 🚀 Diffuser la nouvelle position aux clients concernés
        // Trouver les courses en recherche où ce chauffeur est dans le rayon
        $courses = ApiCourse::where('statut', 'en_attente')
            ->where('chauffeur_id', null)
            ->get();

        foreach ($courses as $course) {
            $distance = $this->calculateDistanceKm(
                $course->depart_latitude,
                $course->depart_longitude,
                $data['latitude'],
                $data['longitude']
            );

            if ($distance <= 5) { // Rayon de 5km
                // Diffuser la mise à jour de position
                $chauffeurData = collect([
                    [
                        'id' => $user->id,
                        'name' => $user->name,
                        'telephone' => $user->telephone,
                        'latitude' => $data['latitude'],
                        'longitude' => $data['longitude'],
                        'est_actif' => true,
                        'distance_km' => round($distance, 2),
                    ]
                ]);
                
                broadcast(new \App\Events\ChauffeursDisponiblesUpdated($course->id, $chauffeurData));
            }
        }

        return response()->json([
            'message' => 'Position mise à jour',
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude']
        ]);
    }

    private function calculateDistanceKm($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Rayon de la Terre en kilomètres

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function findDriver(array $data): ?Utilisateur
    {
        return Utilisateur::where('role', 'Chauffeur')
            ->when(! empty($data['email']), fn ($query) => $query->where('email', $data['email']))
            ->when(! empty($data['telephone']), fn ($query) => $query->where('telephone', $data['telephone']))
            ->first();
    }
}
