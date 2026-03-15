<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Utilisateur::updateOrCreate(
            ['email' => 'admin@cartesscolaires.com'],
            [
                'utilisateur_id' => (string) Str::uuid(),
                'nom' => 'Admin',
                'prenom' => 'Cartes',
                'telephone' => '0000000000',
                'mot_de_passe' => Hash::make('00000000'),
                'role' => 'Admin',
                'statut' => 'libre',
            ]
        );
    }
}
