<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\TripTrackingController;
use App\Http\Controllers\Api\WalletController;

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
    Route::post('/courses/{id}/otp-verify', [CourseController::class, 'verifierOtp']);
    Route::post('/courses/{id}/annuler', [CourseController::class, 'annuler']);
    Route::get('/courses/mes-courses', [CourseController::class, 'mesCourses']);
    Route::get('/courses/{id}', [CourseController::class, 'details']);
    Route::post('/courses/{id}/client-rating', [RatingController::class, 'rateClient']);
    Route::post('/courses/{id}/tracking', [TripTrackingController::class, 'store']);
    Route::get('/courses/{id}/tracking', [TripTrackingController::class, 'index']);
    Route::get('/courses/{id}/tracking/latest', [TripTrackingController::class, 'latest']);

    Route::get('/client/profil', [ClientController::class, 'profil']);
    Route::put('/client/profil', [ClientController::class, 'modifierProfil']);

    Route::post('/notifications/notifier/{course_id}', [NotificationController::class, 'notifierChauffeurs']);
    Route::get('/notifications/mes-notifications', [NotificationController::class, 'mesNotifications']);
    Route::get('/notifications/historique', [NotificationController::class, 'historique']);
    Route::post('/notifications/{id}/compte-a-rebours', [NotificationController::class, 'demarrerCompteARebours']);
    Route::put('/notifications/{id}/vue', [NotificationController::class, 'marquerVue']);
    Route::get('/clients/{id}/ratings', [RatingController::class, 'clientRatings']);
    Route::get('/wallet', [WalletController::class, 'show']);
    Route::get('/wallet/transactions', [WalletController::class, 'transactions']);
    Route::post('/wallet/payout-requests', [WalletController::class, 'requestPayout']);
    Route::get('/wallet/payout-requests', [WalletController::class, 'payoutRequests']);
    Route::get('/admin/payout-requests', [WalletController::class, 'adminPayoutRequests']);
    Route::patch('/admin/payout-requests/{id}', [WalletController::class, 'updatePayoutStatus']);
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::get('/documents/{id}', [DocumentController::class, 'show']);
    Route::post('/documents/{id}/review', [DocumentController::class, 'review']);
    Route::get('/admin/drivers/validation', [AdminController::class, 'driversNeedingValidation']);
});
