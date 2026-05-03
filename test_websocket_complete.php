<?php

require_once __DIR__ . '/vendor/autoload.php';

// Démarrer l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 Test complet de l'intégration WebSocket\n\n";

// 1. Créer une position de tracking
echo "1. Création d'une position de tracking...\n";
$track = \App\Models\ApiCourseTrack::create([
    'course_id' => 59,
    'chauffeur_id' => 3,
    'latitude' => 3.8510,
    'longitude' => 11.5040,
    'bearing' => 60.0,
    'speed' => 35.5,
    'recorded_at' => now(),
]);

echo "✅ Track créé avec ID: {$track->id}\n";

// 2. Créer et broadcaster l'événement
echo "2. Création et broadcast de l'événement...\n";
$event = new \App\Events\DriverLocationUpdated($track);

echo "✅ Événement créé\n";

// 3. Vérifier le channel de broadcast
echo "3. Vérification du channel de broadcast...\n";
$channels = $event->broadcastOn();
echo "📡 Channel: " . $channels[0]->name . "\n";

// 4. Vérifier le nom de l'événement
echo "4. Vérification du nom de l'événement...\n";
$eventName = $event->broadcastAs();
echo "📢 Événement: $eventName\n";

// 5. Vérifier le payload
echo "5. Vérification du payload...\n";
$payload = $event->broadcastWith();
echo "📦 Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n";

// 6. Simuler le broadcast
echo "6. Simulation du broadcast...\n";
try {
    broadcast($event)->toOthers();
    echo "✅ Broadcast réussi\n";
} catch (Exception $e) {
    echo "❌ Erreur broadcast: " . $e->getMessage() . "\n";
}

echo "\n🎉 Test terminé !\n";
echo "📋 Résumé:\n";
echo "   - Channel: course.59 (private)\n";
echo "   - Événement: driver.location.updated\n";
echo "   - Payload: course_id, chauffeur_id, latitude, longitude, bearing, speed, recorded_at\n";
echo "   - Serveur Reverb: localhost:8080\n";
echo "   - Clé Reverb: jok7ds3jzeq09vremxld\n\n";

echo "🔧 Pour tester côté client:\n";
echo "1. Démarrer l'application Flutter\n";
echo "2. Se connecter avec un compte client\n";
echo "3. Accéder à une course avec ID 59\n";
echo "4. L'application devrait s'abonner automatiquement au WebSocket\n";
echo "5. Exécuter ce script pour envoyer des positions\n\n";
