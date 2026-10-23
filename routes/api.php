<?php

use App\Http\Controllers\Api\V1\SpeakerController;
use App\Http\Controllers\Api\V1\AgendaController;
use App\Http\Controllers\Api\V1\DownloadController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\SponsorController;
use App\Http\Controllers\Api\V1\WorldEventController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:sanctum']], function () {});

Route::prefix('v1/auth')->name('api.v1.auth.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::put('profile', [AuthController::class, 'updateProfile'])->name('profile');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
});

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('agenda', [AgendaController::class, 'index'])->name('agenda.index');
    Route::get('agenda/{agendaItem}', [AgendaController::class, 'show'])->name('agenda.show');
    Route::get('downloads', [DownloadController::class, 'index'])->name('downloads.index');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('sponsors', [SponsorController::class, 'index'])->name('sponsors.index');
    Route::get('sponsors/{sponsor}', [SponsorController::class, 'show'])->name('sponsors.show');
    Route::get('speakers', [SpeakerController::class, 'index'])->name('speakers.index');
    Route::get('speakers/{speaker}', [SpeakerController::class, 'show'])->name('speakers.show');
    Route::get('world-events', [WorldEventController::class, 'index'])->name('world-events.index');
});
