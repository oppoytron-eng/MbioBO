<?php

namespace Database\Seeders;

use App\Models\Chauffeur;
use App\Models\ChauffeurDocument;
use App\Models\Client;
use App\Models\Course;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Création de clients de démonstration et de leurs courses');

        $this->cleanupDemoUsers([
            'chauffeur.simon@demo.local',
            'chauffeur.nina@demo.local',
            'client.awa@demo.local',
            'client.mamadou@demo.local',
            'client.aissatou@demo.local',
        ]);

        $chauffeurs = [
            'simon' => $this->createChauffeur(
                prenom: 'Simon',
                nom: 'Diop',
                email: 'chauffeur.simon@demo.local',
                telephone: '771234501',
                numeroPermis: 'CH-8271'
            ),
            'nina' => $this->createChauffeur(
                prenom: 'Nina',
                nom: 'Ba',
                email: 'chauffeur.nina@demo.local',
                telephone: '771234502',
                numeroPermis: 'CH-8272'
            ),
        ];

        $this->createClientProfile(
            info: [
                'prenom' => 'Awa',
                'nom' => 'Sow',
                'email' => 'client.awa@demo.local',
                'telephone' => '770000001',
                'note_moyenne' => 4.8,
                'est_actif' => true,
            ],
            courseSeeds: [
                [
                    'chauffeur' => $chauffeurs['simon'],
                    'statut' => 'En course',
                    'adresse_depart' => 'Station Liberté 6',
                    'lat_depart' => 14.7167,
                    'lng_depart' => -17.4500,
                    'adresse_arrivee' => 'Rue des Almadies',
                    'lat_arrivee' => 14.6945,
                    'lng_arrivee' => -17.4490,
                    'distance_km' => 7.4,
                    'prix_estime' => 5200,
                    'prix_final' => 5400,
                    'modePaiement' => 'MOMO_MTN',
                    'termine_le' => Carbon::now()->subDays(1)->subHours(2),
                ],
                [
                    'chauffeur' => $chauffeurs['nina'],
                    'statut' => 'En ligne',
                    'adresse_depart' => 'Place de l\'Indépendance',
                    'lat_depart' => 14.6937,
                    'lng_depart' => -17.4448,
                    'adresse_arrivee' => 'Aéroport Blaise Diagne',
                    'lat_arrivee' => 14.7399,
                    'lng_arrivee' => -17.4731,
                    'distance_km' => 32.1,
                    'prix_estime' => 12500,
                    'prix_final' => 12000,
                    'modePaiement' => 'CASH',
                    'termine_le' => Carbon::now()->subDays(3)->subHours(1),
                ],
                [
                    'chauffeur' => $chauffeurs['simon'],
                    'statut' => 'En ATTENTE',
                    'adresse_depart' => 'Pont de lâ€™Arc en Ciel',
                    'lat_depart' => 14.7133,
                    'lng_depart' => -17.4455,
                    'adresse_arrivee' => 'Marché Sandaga',
                    'lat_arrivee' => 14.6951,
                    'lng_arrivee' => -17.4449,
                    'distance_km' => 1.8,
                    'prix_estime' => 2200,
                    'prix_final' => 2100,
                    'modePaiement' => 'ORANGE_MONEY',
                    'termine_le' => Carbon::now()->subDays(5)->subHours(4),
                ],
            ]
        );

        $this->createClientProfile(
            info: [
                'prenom' => 'Mamadou',
                'nom' => 'Ndiaye',
                'email' => 'client.mamadou@demo.local',
                'telephone' => '770000002',
                'note_moyenne' => 4.2,
                'est_actif' => false,
            ],
            courseSeeds: [
                [
                    'chauffeur' => $chauffeurs['nina'],
                    'statut' => 'En ligne',
                    'adresse_depart' => 'Université Gaston Berger',
                    'lat_depart' => 14.8319,
                    'lng_depart' => -16.8407,
                    'adresse_arrivee' => 'Centre-ville de Dakar',
                    'lat_arrivee' => 14.6928,
                    'lng_arrivee' => -17.4455,
                    'distance_km' => 4.6,
                    'prix_estime' => 4800,
                    'prix_final' => 4700,
                    'modePaiement' => 'MOMO_MTN',
                    'termine_le' => Carbon::now()->subDays(2),
                ],
                [
                    'chauffeur' => $chauffeurs['simon'],
                    'statut' => 'En course',
                    'adresse_depart' => 'Hôpital Principal',
                    'lat_depart' => 14.6911,
                    'lng_depart' => -17.4444,
                    'adresse_arrivee' => 'Vivre Ensemble',
                    'lat_arrivee' => 14.7160,
                    'lng_arrivee' => -17.4567,
                    'distance_km' => 5.9,
                    'prix_estime' => 5900,
                    'prix_final' => 6000,
                    'modePaiement' => 'CASH',
                    'termine_le' => Carbon::now()->subDays(6),
                ],
            ]
        );

        $this->createClientProfile(
            info: [
                'prenom' => 'Aissatou',
                'nom' => 'Diallo',
                'email' => 'client.aissatou@demo.local',
                'telephone' => '770000003',
                'note_moyenne' => 3.9,
                'est_actif' => false,
            ],
            courseSeeds: [
                [
                    'chauffeur' => $chauffeurs['nina'],
                    'statut' => 'Hors ligne',
                    'adresse_depart' => 'Centre médical SOS',
                    'lat_depart' => 14.7011,
                    'lng_depart' => -17.4533,
                    'adresse_arrivee' => 'La Corniche',
                    'lat_arrivee' => 14.7072,
                    'lng_arrivee' => -17.4707,
                    'distance_km' => 3.1,
                    'prix_estime' => 2800,
                    'prix_final' => 3000,
                    'modePaiement' => 'ORANGE_MONEY',
                    'termine_le' => Carbon::now()->subDays(8),
                ],
            ],
            archive: true
        );
    }

    private function createChauffeur(string $prenom, string $nom, string $email, string $telephone, string $numeroPermis): Chauffeur
    {
        $utilisateur = $this->createUtilisateur($email, $prenom, $nom, $telephone, 'Chauffeur');

        $chauffeur = Chauffeur::firstOrNew(['utilisateur_id' => $utilisateur->utilisateur_id]);

        if (! $chauffeur->exists) {
            $chauffeur->id = (string) Str::uuid();
        }

        $chauffeur->fill([
            'numero_permis' => $numeroPermis,
            'statut' => 'En ligne',
            'note_moyenne' => 4.7,
            'nb_courses' => 128,
            'solde_total' => 950000,
            'lat_actuelle' => 14.6928,
            'lng_actuelle' => -17.4467,
            'est_actif' => true,
            'statut_operationnel' => 'actif',
            'etat_documents' => 'pending',
        ]);

        $chauffeur->save();
        $this->seedDocuments($chauffeur);

        return $chauffeur;
    }

    private function createClientProfile(array $info, array $courseSeeds, bool $archive = false): Client
    {
        $utilisateur = $this->createUtilisateur(
            $info['email'],
            $info['prenom'],
            $info['nom'],
            $info['telephone'],
            'Client'
        );

        $client = Client::firstOrNew(['utilisateur_id' => $utilisateur->utilisateur_id]);

        if (! $client->exists) {
            $client->id = (string) Str::uuid();
        }

        $client->fill([
            'note_moyenne' => $info['note_moyenne'] ?? 0,
            'nb_courses' => count($courseSeeds),
            'est_actif' => $info['est_actif'] ?? true,
        ]);

        $client->save();

        foreach ($courseSeeds as $courseSeed) {
            $this->createCourse($client, $courseSeed);
        }

        if ($archive) {
            $client->delete();
        }

        return $client;
    }

    private function createCourse(Client $client, array $seed): Course
    {
        /** @var Chauffeur $chauffeur */
        $chauffeur = $seed['chauffeur'];
        unset($seed['chauffeur']);

        $defaults = [
            'statut' => 'En ATTENTE',
            'adresse_depart' => 'Point de départ par défaut',
            'lat_depart' => 14.6920,
            'lng_depart' => -17.4430,
            'adresse_arrivee' => 'Point d\'arrivée par défaut',
            'lat_arrivee' => 14.6950,
            'lng_arrivee' => -17.4410,
            'distance_km' => 1.5,
            'prix_estime' => 2000,
            'prix_final' => 2000,
            'modePaiement' => 'CASH',
            'termine_le' => Carbon::now()->subDays(1),
            'demande_le' => Carbon::now()->subDays(2),
            'est_annule' => $seed['est_annule'] ?? false,
            'est_terminee' => $seed['est_terminee'] ?? false,
        ];

        $courseData = array_merge($defaults, $seed);

        return Course::create([
            'client_id' => $client->id,
            'chauffeur_id' => $chauffeur->id,
        ] + $courseData);
    }

    private function createUtilisateur(string $email, string $prenom, string $nom, string $telephone, string $role): Utilisateur
    {
        $utilisateur = Utilisateur::firstOrNew(['email' => $email]);

        if (! $utilisateur->exists) {
            $utilisateur->utilisateur_id = (string) Str::uuid();
        }

        $utilisateur->nom = $nom;
        $utilisateur->prenom = $prenom;
        $utilisateur->telephone = $telephone;
        $utilisateur->mot_de_passe = Hash::make('demo-password');
        $utilisateur->role = $role;
        $utilisateur->statut = 'libre';
        $utilisateur->remember_token = Str::random(10);
        $utilisateur->save();

        return $utilisateur;
    }

    private function seedDocuments(Chauffeur $chauffeur): void
    {
        $definitions = [
            ['type' => 'permis de conduire', 'status' => 'validated', 'path' => 'docs/permis.pdf'],
            ['type' => 'carte grise', 'status' => 'pending', 'path' => 'docs/carte-grise.pdf'],
            ['type' => 'assurance', 'status' => 'pending', 'path' => 'docs/assurance.pdf'],
        ];

        foreach ($definitions as $definition) {
            ChauffeurDocument::updateOrCreate(
                [
                    'chauffeur_id' => $chauffeur->id,
                    'type' => $definition['type'],
                ],
                [
                    'status' => $definition['status'],
                    'path' => $definition['path'],
                    'notes' => null,
                ]
            );
        }
    }

    private function cleanupDemoUsers(array $emails): void
    {
        DB::transaction(function () use ($emails) {
            $utilisateurs = Utilisateur::whereIn('email', $emails)->get();

            foreach ($utilisateurs as $utilisateur) {
                $client = Client::withTrashed()
                    ->where('utilisateur_id', $utilisateur->utilisateur_id)
                    ->first();
                $chauffeur = $utilisateur->chauffeur;

                if ($client) {
                    Course::where('client_id', $client->id)->delete();
                    $client->forceDelete();
                }

                if ($chauffeur) {
                    Course::where('chauffeur_id', $chauffeur->id)->delete();
                    ChauffeurDocument::where('chauffeur_id', $chauffeur->id)->delete();
                    $chauffeur->forceDelete();
                }

                $utilisateur->forceDelete();
            }
        });
    }
}
