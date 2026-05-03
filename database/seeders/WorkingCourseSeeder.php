<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WorkingCourseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Créer les utilisateurs d'abord
        $clientId = (string) Str::uuid();
        $chauffeurId = (string) Str::uuid();

        DB::table('utilisateurs')->insert([
            [
                'utilisateur_id' => $clientId,
                'nom' => 'Diop',
                'prenom' => 'Moussa',
                'email' => 'client.test@example.com',
                'telephone' => '+221771234999',
                'mot_de_passe' => Hash::make('password123'),
                'role' => 'Client',
                'statut' => 'libre',
                'est_actif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'utilisateur_id' => $chauffeurId,
                'nom' => 'Ndiaye',
                'prenom' => 'Omar',
                'email' => 'chauffeur.test@example.com',
                'telephone' => '+221783456999',
                'mot_de_passe' => Hash::make('password123'),
                'role' => 'Chauffeur',
                'statut' => 'libre',
                'est_actif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 2. Créer les enregistrements clients/chauffeurs
        $clientRecordId = (string) Str::uuid();
        $chauffeurRecordId = (string) Str::uuid();

        DB::table('clients')->insert([
            'id' => $clientRecordId,
            'utilisateur_id' => $clientId,
            'note_moyenne' => 4.5,
            'nb_courses' => 0,
        ]);

        DB::table('chauffeurs')->insert([
            'id' => $chauffeurRecordId,
            'utilisateur_id' => $chauffeurId,
            'numero_permis' => 'SN123456',
            'statut' => 'En ligne',
            'statut_operationnel' => 'actif',
            'note_moyenne' => 4.2,
            'nb_courses' => 0,
            'solde_total' => 25000.00,
            'lat_actuelle' => 14.6996,
            'lng_actuelle' => -17.4430,
            'est_actif' => true,
            'etat_documents' => 'valide',
        ]);

        // 3. Créer les courses avec les bons IDs
        $courses = [
            [
                'id' => (string) Str::uuid(),
                'client_id' => $clientRecordId,
                'chauffeur_id' => $chauffeurRecordId,
                'statut' => 'En course',
                'lat_depart' => 14.6996,
                'lng_depart' => -17.4430,
                'lat_arrivee' => 14.7167,
                'lng_arrivee' => -17.4677,
                'adresse_depart' => 'Plateau, Dakar',
                'adresse_arrivee' => 'Aéroport International Blaise Diagne',
                'distance_km' => 45.5,
                'prix_estime' => 15000,
                'prix_final' => 15000,
                'est_actif' => 1,
                'modePaiement' => 'CASH',
                'demande_le' => now()->subMinutes(30),
                'termine_le' => now()->addHours(2),
            ],
            [
                'id' => (string) Str::uuid(),
                'client_id' => $clientRecordId,
                'chauffeur_id' => $chauffeurRecordId,
                'statut' => 'En ATTENTE',
                'lat_depart' => 14.7167,
                'lng_depart' => -17.4677,
                'lat_arrivee' => 14.6996,
                'lng_arrivee' => -17.4430,
                'adresse_depart' => 'Aéroport International Blaise Diagne',
                'adresse_arrivee' => 'Plateau, Dakar',
                'distance_km' => 45.5,
                'prix_estime' => 15000,
                'prix_final' => 15000,
                'est_actif' => 1,
                'modePaiement' => 'ORANGE_MONEY',
                'demande_le' => now()->addMinutes(10),
                'termine_le' => now()->addHours(2),
            ]
        ];

        foreach ($courses as $courseData) {
            DB::table('courses')->insert($courseData);
        }

        $this->command->info('✅ Courses de test créées avec succès !');
        $this->command->info('📱 Client: client.test@example.com | Mot de passe: password123');
        $this->command->info('🚗 Chauffeur: chauffeur.test@example.com | Mot de passe: password123');
        $this->command->info('🎯 Courses créées: ' . count($courses));
    }
}
