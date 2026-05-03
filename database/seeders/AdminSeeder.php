<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str; // <-- Ajoutez cet import

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Utilisateur::updateOrCreate(
            ['email' => 'admin@exemple.com'],
            [
                'id' => (string) Str::uuid(), // <-- Génère l'UUID côté PHP
                'nom' => 'Administrateur',
                'prenom' => 'System',
                'telephone' => '0123456789',
                'mot_de_passe' => Hash::make('00000000'),
                'role' => 'Admin',
                'statut' => 'libre',
                'est_actif' => true,
            ]
        );
    }
}
