<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AppSettingsController;
use App\Http\Controllers\Admin\ChauffeurController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');

    Route::middleware('auth:admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('action', [DashboardController::class, 'performAction'])->name('action');
        Route::post('sessions/{session}/terminate', [DashboardController::class, 'terminateSession'])->name('sessions.terminate');
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::prefix('clients')->name('clients.')->group(function () {
            Route::get('/', [ClientController::class, 'index'])->name('index');
            Route::get('{client}', [ClientController::class, 'show'])->name('show');
            Route::post('{client}/toggle-active', [ClientController::class, 'toggleActive'])->name('toggle-active');
            Route::post('{client}/archive', [ClientController::class, 'archive'])->name('archive');
            Route::post('{client}/restore', [ClientController::class, 'restore'])->name('restore');
        });

        Route::prefix('courses')->name('courses.')->group(function () {
            Route::get('/', [CourseController::class, 'index'])->name('index');
            Route::get('{course}', [CourseController::class, 'show'])->name('show');
            Route::post('{course}/cancel', [CourseController::class, 'cancel'])->name('cancel');
            Route::post('{course}/complete', [CourseController::class, 'complete'])->name('complete');
        });

        Route::prefix('chauffeurs')->name('chauffeurs.')->group(function () {
            Route::get('/', [ChauffeurController::class, 'index'])->name('index');
            Route::get('{chauffeur}', [ChauffeurController::class, 'show'])->name('show');
            Route::post('{chauffeur}/status', [ChauffeurController::class, 'toggleStatus'])->name('status');
            Route::post('{chauffeur}/archive', [ChauffeurController::class, 'archive'])->name('archive');
            Route::post('{chauffeur}/restore', [ChauffeurController::class, 'restore'])->name('restore');
            Route::post('{chauffeur}/documents/{document}/review', [ChauffeurController::class, 'reviewDocument'])->name('documents.review');
        });

        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::post('/', [NotificationController::class, 'store'])->name('store');
            Route::delete('{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [AdminRoleController::class, 'index'])->name('index');
            Route::post('/', [AdminRoleController::class, 'store'])->name('store');
            Route::post('{role}/utilisateur/{utilisateur}', [AdminRoleController::class, 'toggle'])->name('toggle');
        });

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [AppSettingsController::class, 'edit'])->name('edit');
            Route::post('/', [AppSettingsController::class, 'update'])->name('update');
        });

        Route::prefix('finances')->name('finances.')->group(function () {
            Route::get('transactions', [FinanceController::class, 'transactions'])->name('transactions');
            Route::get('chauffeurs/{chauffeur}/balance', [FinanceController::class, 'chauffeurBalance'])->name('chauffeurs.balance');
            Route::get('withdrawals', [FinanceController::class, 'withdrawals'])->name('withdrawals');
            Route::post('withdrawals/{withdrawal}/approve', [FinanceController::class, 'approveWithdrawal'])->name('withdrawals.approve');
            Route::post('withdrawals/{withdrawal}/reject', [FinanceController::class, 'rejectWithdrawal'])->name('withdrawals.reject');
        });
    });
});
