<?php

require_once __DIR__ . '/vendor/autoload.php';

// Démarrer l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🚗 Simulation de suivi GPS en temps réel\n";
echo "Course ID: 61 | Chauffeur ID: 3\n";
echo "Envoi de positions toutes les 2 secondes...\n";
echo "Appuyez sur Ctrl+C pour arrêter\n\n";

$lat = 3.8480;
$lng = 11.5021;
$bearing = 0;
$speed = 25;

while (true) {
    try {
        // Simuler un mouvement
        $lat += rand(1, 10) * 0.0001;
        $lng += rand(1, 10) * 0.0001;
        $bearing = ($bearing + rand(5, 15)) % 360;
        $speed = rand(20, 40);
        
        // Créer une position
        $track = \App\Models\ApiCourseTrack::create([
            'course_id' => 61,
            'chauffeur_id' => 3,
            'latitude' => $lat,
            'longitude' => $lng,
            'bearing' => $bearing,
            'speed' => $speed,
            'recorded_at' => now(),
        ]);
        
        // Créer et broadcaster l'événement
        $event = new \App\Events\DriverLocationUpdated($track);
        broadcast($event)->toOthers();
        
        echo "📍 [" . date('H:i:s') . "] Position envoyée: {$lat}, {$lng} (vitesse: {$speed}km/h, cap: {$bearing}°)\n";
        
        // Attendre 2 secondes
        sleep(2);
        
    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
        sleep(2);
    }
}
