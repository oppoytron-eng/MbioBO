<?php
require __DIR__ . '/vendor/autoload.php';
 = require_once __DIR__ . '/bootstrap/app.php';
 = ->make(Illuminate\\Contracts\\Http\\Kernel::class);
 = Illuminate\\Http\\Request::create('/api/drivers/register', 'POST', [
    'nom' => 'Moussa',
    'prenom' => 'Fall',
    'email' => 'driver@example.com',
    'telephone' => '+221771234567',
    'mot_de_passe' => 'secret123',
    'mot_de_passe_confirmation' => 'secret123',
    'numero_permis' => 'SN-123456',
    'lat_actuelle' => 14.7,
    'lng_actuelle' => -17.45,
]);
 = ->handle();
echo ->getStatusCode() . ' ' . ->getContent();
