<?php

require_once __DIR__ . '/vendor/autoload.php';

// Démarrer l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🚗 Simulation de déplacement des chauffeurs en temps réel\n";
echo "Course ID: 67 | Mise à jour des positions toutes les 3 secondes\n";
echo "Appuyez sur Ctrl+C pour arrêter\n\n";

// ID des chauffeurs actifs
$chauffeursIds = [3, 4, 5, 6];

// Coordonnées de base (Yaoundé)
$centerLat = 3.8480;
$centerLng = 11.5020;

// Positions initiales pour chaque chauffeur
$positions = [
    3 => ['lat' => 3.8490, 'lng' => 11.5030, 'bearing' => 0],
    4 => ['lat' => 3.8500, 'lng' => 11.5040, 'bearing' => 90],
    5 => ['lat' => 3.8520, 'lng' => 11.5060, 'bearing' => 180],
    6 => ['lat' => 3.8540, 'lng' => 11.5080, 'bearing' => 270],
];

while (true) {
    try {
        foreach ($chauffeursIds as $chauffeurId) {
            if (!isset($positions[$chauffeurId])) continue;

            $pos = &$positions[$chauffeurId];
            
            // Simuler un mouvement lent
            $pos['lat'] += rand(-2, 2) * 0.0001;
            $pos['lng'] += rand(-2, 2) * 0.0001;
            $pos['bearing'] = ($pos['bearing'] + rand(-10, 10)) % 360;

            // Mettre à jour la position du chauffeur
            $chauffeur = \App\Models\User::find($chauffeurId);
            if ($chauffeur) {
                $chauffeur->latitude = $pos['lat'];
                $chauffeur->longitude = $pos['lng'];
                $chauffeur->save();
            }

            // Calculer la distance depuis le centre
            $distance = calculateDistanceKm($centerLat, $centerLng, $pos['lat'], $pos['lng']);
            
            echo "🚗 Chauffeur $chauffeurId: {$pos['lat']}, {$pos['lng']} ({$distance}km du départ)\n";
        }

        echo "---\n";
        sleep(3);
    } catch (Exception $e) {
        echo "❌ Erreur: {$e->getMessage()}\n";
        sleep(3);
    }
}

function calculateDistanceKm($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // Rayon de la Terre en mètres

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

    return $earthRadius * $c / 1000; // Convertir en km
}
