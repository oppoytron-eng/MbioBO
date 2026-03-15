<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\NotificationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/courses', [CourseController::class, 'creer']);
    Route::get('/courses/disponibles', [CourseController::class, 'disponibles']);
    Route::post('/courses/{id}/accepter', [CourseController::class, 'accepter']);
    Route::post('/courses/{id}/demarrer', [CourseController::class, 'demarrer']);
    Route::post('/courses/{id}/terminer', [CourseController::class, 'terminer']);
    Route::post('/courses/{id}/annuler', [CourseController::class, 'annuler']);
    Route::get('/courses/mes-courses', [CourseController::class, 'mesCourses']);
    Route::get('/courses/{id}', [CourseController::class, 'details']);

    Route::get('/client/profil', [ClientController::class, 'profil']);
    Route::put('/client/profil', [ClientController::class, 'modifierProfil']);

    Route::post('/notifications/notifier/{course_id}', [NotificationController::class, 'notifierChauffeurs']);
    Route::get('/notifications/mes-notifications', [NotificationController::class, 'mesNotifications']);
    Route::put('/notifications/{id}/vue', [NotificationController::class, 'marquerVue']);
});
