<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chauffeur;
use App\Models\Utilisateur;
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
            'numero_permis' => ['required', 'string', 'max:50', 'unique:chauffeurs,numero_permis'],
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
            'numero_permis' => $data['numero_permis'],
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

    private function findDriver(array $data): ?Utilisateur
    {
        return Utilisateur::where('role', 'Chauffeur')
            ->when(! empty($data['email']), fn ($query) => $query->where('email', $data['email']))
            ->when(! empty($data['telephone']), fn ($query) => $query->where('telephone', $data['telephone']))
            ->first();
    }
}
