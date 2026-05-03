<?php

require_once __DIR__ . '/vendor/autoload.php';

// Démarrer l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🚗 Ajout de positions de chauffeurs pour tester le système\n\n";

// Coordonnées de Yaoundé (centre)
$centerLat = 3.8480;
$centerLng = 11.5020;

// Créer ou mettre à jour des chauffeurs avec des positions
$chauffeursData = [
    ['id' => 3, 'name' => 'Chauffeur Paul', 'lat' => 3.8490, 'lng' => 11.5030], // ~1.5km
    ['id' => 4, 'name' => 'Chauffeur Marie', 'lat' => 3.8500, 'lng' => 11.5040], // ~2.2km
    ['id' => 5, 'name' => 'Chauffeur Jean', 'lat' => 3.8520, 'lng' => 11.5060], // ~4.1km
    ['id' => 6, 'name' => 'Chauffeur Alice', 'lat' => 3.8540, 'lng' => 11.5080], // ~6.2km (hors rayon)
];

foreach ($chauffeursData as $data) {
    $chauffeur = \App\Models\User::find($data['id']);
    
    if ($chauffeur) {
        // Mettre à jour la position du chauffeur
        $chauffeur->latitude = $data['lat'];
        $chauffeur->longitude = $data['lng'];
        $chauffeur->est_actif = true;
        $chauffeur->save();
        
        // Calculer la distance depuis le centre
        $distance = calculateDistanceKm($centerLat, $centerLng, $data['lat'], $data['lng']);
        
        echo "✅ {$data['name']} (ID: {$data['id']}) mis à jour\n";
        echo "   📍 Position: {$data['lat']}, {$data['lng']}\n";
        echo "   📏 Distance: " . round($distance, 2) . "km\n";
        echo "   🎯 " . ($distance <= 5 ? "DANS le rayon de 5km" : "HORS rayon de 5km") . "\n\n";
    } else {
        echo "❌ Chauffeur ID {$data['id']} non trouvé\n";
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

echo "🎯 Test terminé ! Les chauffeurs sont maintenant positionnés.\n";
echo "📱 Créez une course depuis l'application Flutter pour tester les notifications.\n";
