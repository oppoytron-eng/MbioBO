<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Events\DriverLocationUpdated;
use App\Models\ApiCourseTrack;
use Illuminate\Http\Request;

// Démarrer l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Test d'émission d'événement WebSocket...\n";

// Créer une position de tracking en base
$track = ApiCourseTrack::create([
    'course_id' => 59,
    'chauffeur_id' => 3,
    'latitude' => 3.8490,
    'longitude' => 11.5030,
    'bearing' => 45.0,
    'speed' => 25.5,
    'recorded_at' => now(),
]);

echo "Track créé avec ID: {$track->id}\n";
echo "Course ID: {$track->course_id}\n";
echo "Position: {$track->latitude}, {$track->longitude}\n";

// Créer et broadcaster l'événement
$event = new DriverLocationUpdated($track);
echo "Événement créé\n";

// Broadcast l'événement
broadcast($event)->toOthers();
echo "Événement broadcasté sur le channel course.{$track->course_id}\n";

echo "Test terminé\n";
