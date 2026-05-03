<?php

use App\Models\ApiCourse;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Channel privé pour le suivi de position d'une course
// Seul le client et le chauffeur de la course peuvent s'abonner
Broadcast::channel('course.{courseId}', function ($user, $courseId) {
    $course = ApiCourse::find($courseId);
    
    if (!$course) {
        return false;
    }
    
    // Autoriser si l'utilisateur est le client ou le chauffeur de la course
    return (int) $user->id === (int) $course->client_id || 
           (int) $user->id === (int) $course->chauffeur_id;
});

// Channel privé pour les chauffeurs
// Seul le chauffeur concerné peut s'abonner
Broadcast::channel('chauffeur.{chauffeurId}', function ($user, $chauffeurId) {
    return (int) $user->id === (int) $chauffeurId && $user->role === 'chauffeur';
});
