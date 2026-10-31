<?php

use App\Http\Controllers\Administrator\CoachController;
use App\Http\Controllers\Administrator\IntelligenceController;
use App\Http\Controllers\Administrator\InventoryController;
use App\Http\Controllers\Administrator\RouteStopController;
use App\Http\Controllers\Administrator\SeatController;
use App\Http\Controllers\Administrator\StationController;
use App\Http\Controllers\Administrator\TrainController;
use App\Http\Controllers\Administrator\TripController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingWizardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TrainSearchController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('auth:web')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::view('/administrator', 'administrator.dashboard')
        ->middleware('can:access-administrator')
        ->name('administrator.dashboard');
});

Route::get('/trains/search', TrainSearchController::class)->name('trains.search');

Route::prefix('administrator/intelligence')->name('administrator.intelligence.')
    ->middleware(['auth:web', 'can:access-administrator'])->group(function () {
        Route::get('/', [IntelligenceController::class, 'index'])->name('index');
        Route::post('/train', [IntelligenceController::class, 'train'])
            ->middleware(['password.confirm', 'throttle:1,10'])->name('train');
    });

Route::middleware('auth:web')->group(function () {
    Route::get('/trips/{trip}/bookings/create', [BookingWizardController::class, 'create'])->name('bookings.create');
    Route::post('/trips/{trip}/booking-drafts', [BookingWizardController::class, 'details'])->name('booking-drafts.details');
    Route::get('/booking-drafts/{draft}/seats', [BookingWizardController::class, 'seats'])->name('booking-drafts.seats');
    Route::post('/booking-drafts/{draft}/review', [BookingWizardController::class, 'review'])->name('booking-drafts.review.store');
    Route::get('/booking-drafts/{draft}/review', [BookingWizardController::class, 'showReview'])->name('booking-drafts.review');
    Route::post('/booking-drafts/{draft}/confirm', [BookingWizardController::class, 'confirm'])->middleware('throttle:30,1')->name('booking-drafts.confirm');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking:reference}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{booking:reference}/cancel', [BookingController::class, 'cancellation'])->name('bookings.cancellation');
    Route::post('/bookings/{booking:reference}/cancel', [BookingController::class, 'cancel'])->middleware('throttle:30,1')->name('bookings.cancel');
});

Route::prefix('administrator')->name('administrator.')
    ->middleware(['auth:web', 'can:access-administrator'])->scopeBindings()->group(function () {
        Route::get('stations', [StationController::class, 'index'])->name('stations.index');
        Route::post('stations', [StationController::class, 'store'])->name('stations.store');
        Route::get('stations/{station}/edit', [StationController::class, 'edit'])->name('stations.edit');
        Route::put('stations/{station}', [StationController::class, 'update'])->name('stations.update');
        Route::get('trains', [TrainController::class, 'index'])->name('trains.index');
        Route::post('trains', [TrainController::class, 'store'])->name('trains.store');
        Route::get('trains/{train}/edit', [TrainController::class, 'edit'])->name('trains.edit');
        Route::put('trains/{train}', [TrainController::class, 'update'])->name('trains.update');
        Route::get('trains/{train}/stops', [RouteStopController::class, 'index'])->name('trains.stops.index');
        Route::post('trains/{train}/stops', [RouteStopController::class, 'store'])->name('trains.stops.store');
        Route::get('trains/{train}/stops/{routeStop}/edit', [RouteStopController::class, 'edit'])->name('trains.stops.edit');
        Route::put('trains/{train}/stops/{routeStop}', [RouteStopController::class, 'update'])->name('trains.stops.update');
        Route::get('trains/{train}/coaches', [CoachController::class, 'index'])->name('trains.coaches.index');
        Route::post('trains/{train}/coaches', [CoachController::class, 'store'])->name('trains.coaches.store');
        Route::get('trains/{train}/coaches/{coach}/edit', [CoachController::class, 'edit'])->name('trains.coaches.edit');
        Route::put('trains/{train}/coaches/{coach}', [CoachController::class, 'update'])->name('trains.coaches.update');
        Route::get('trains/{train}/coaches/{coach}/seats', [SeatController::class, 'index'])->name('trains.coaches.seats.index');
        Route::post('trains/{train}/coaches/{coach}/seats', [SeatController::class, 'store'])->name('trains.coaches.seats.store');
        Route::get('trains/{train}/coaches/{coach}/seats/{seat}/edit', [SeatController::class, 'edit'])->name('trains.coaches.seats.edit');
        Route::put('trains/{train}/coaches/{coach}/seats/{seat}', [SeatController::class, 'update'])->name('trains.coaches.seats.update');
        Route::get('trips', [TripController::class, 'index'])->name('trips.index');
        Route::get('trains/{train}/trips', [TripController::class, 'forTrain'])->name('trains.trips.index');
        Route::post('trains/{train}/trips', [TripController::class, 'store'])->name('trains.trips.store');
        Route::get('trips/{trip}/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('trips/{trip}/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    });
