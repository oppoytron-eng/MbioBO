<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Vérifier que l'email existe
        $user = User::where('email', $data['email'])->first();
        
        if (!$user) {
            return response()->json([
                'message' => 'Aucun compte trouvé avec cet email'
            ], 404);
        }

        // Générer un token unique
        $token = Str::random(60);
        
        // Stocker le token en base
        PasswordResetToken::create([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now(),
        ]);

        // Générer le code à 6 chiffres
        $code = substr(str_shuffle('0123456789'), 0, 6);

        // Créer le lien deep link
        $resetLink = "mbio://reset-password?token={$token}&email=" . urlencode($user->email);

        // Envoyer l'email
        try {
            // Mail::to($user->email)->send(new PasswordResetMail(
            //     $user->name,
            //     $code,
            //     $resetLink
            // ));

            return response()->json([
                'message' => 'Email de réinitialisation envoyé avec succès (TEST)',
                'email' => $user->email,
                'debug_token' => $token,
                'debug_code' => $code
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            \Log::info('Reset password attempt', ['data' => $request->all()]);
            
            $data = $request->validate([
                'email' => ['required', 'email'],
                'token' => ['required', 'string'],
                'password' => ['required', 'string', 'min:6'],
                'password_confirmation' => ['required', 'same:password'],
            ]);

            \Log::info('Validation passed', ['data' => $data]);

            // Vérifier le token
            $resetToken = PasswordResetToken::where('email', $data['email'])
                ->where('token', $data['token'])
                ->where('created_at', '>', now()->subMinutes(60))
                ->first();

            \Log::info('Token search', [
                'email' => $data['email'], 
                'token' => $data['token'],
                'found' => $resetToken ? 'YES' : 'NO'
            ]);

            if (!$resetToken) {
                return response()->json([
                    'message' => 'Token invalide ou expiré',
                    'debug' => [
                        'email' => $data['email'],
                        'token' => $data['token'],
                        'time_limit' => now()->subMinutes(60),
                    ]
                ], 400);
            }

            // Mettre à jour le mot de passe
            \Log::info('Updating user password', ['email' => $data['email']]);
            $user = User::where('email', $data['email'])->first();
            
            if (!$user) {
                \Log::error('User not found', ['email' => $data['email']]);
                return response()->json(['message' => 'Utilisateur non trouvé'], 404);
            }
            
            \Log::info('User found', ['id' => $user->id, 'name' => $user->name]);
            
            $user->update([
                'password' => Hash::make($data['password'])
            ]);
            
            \Log::info('Password updated successfully');

            // Supprimer le token utilisé
            $resetToken->delete();
            \Log::info('Token deleted');

            return response()->json([
                'message' => 'Mot de passe réinitialisé avec succès'
            ]);
        } catch (\Exception $e) {
            \Log::error('Reset password error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}
