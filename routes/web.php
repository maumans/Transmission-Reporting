<?php

use App\Http\Controllers\AnnexeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\TransmissionController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\ParametreController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware('auth')->group(function () {
    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

    // Routes des transmissions
    Route::resource('transmission', TransmissionController::class);
    Route::get('/transmission/{transmission}/execute', [TransmissionController::class, 'executeOperation'])->name('transmission.execute');
    Route::get('/transmission/{transmission}/validate', [TransmissionController::class, 'validate'])->name('transmission.validate');
    Route::get('/transmission/{transmission}/reject', [TransmissionController::class, 'reject'])->name('transmission.reject');
    Route::get('/transmission/{transmission}/transmit', [TransmissionController::class, 'transmit'])->name('transmission.transmit');
    Route::get('/transmission/{transmission}/calculate/{rubrique}', [TransmissionController::class, 'calculate'])->name('transmission.calculate');
    Route::get('/transmission/{transmission}/print/{rubrique}', [TransmissionController::class, 'print'])->name('transmission.print');
    Route::get('/transmission/{transmission}/download', [TransmissionController::class, 'download'])
        ->name('transmission.download')
        ->middleware('role:admin|valideur');
    Route::get('/transmission/{transmissionId}/calcul-status', [TransmissionController::class, 'getCalculStatus'])->name('transmission.calcul-status');

    // Routes des balances
    Route::resource('balance', BalanceController::class);
    Route::get('/balance/{balance}/execute', [BalanceController::class, 'executeOperation'])->name('balance.execute');
    Route::get('/balance/{balance}/validate', [BalanceController::class, 'validate'])->name('balance.validate');
    Route::get('/balance/{balance}/reject', [BalanceController::class, 'reject'])->name('balance.reject');
    Route::get('/balance/{balance}/transmission', [BalanceController::class, 'transmission'])->name('balance.transmission');


    // Routes des annexes
    Route::resource('annexe', AnnexeController::class);
    Route::post('/annexe/{annexe}/execute', [AnnexeController::class, 'executeOperation'])->name('annexe.execute');
    Route::post('/annexe/{annexe}/validate', [AnnexeController::class, 'validate'])->name('annexe.validate');
    Route::post('/annexe/{annexe}/reject', [AnnexeController::class, 'reject'])->name('annexe.reject');


    // Routes des opérations
    Route::resource('operation', OperationController::class);
    Route::post('/operation/{operation}/execute', [OperationController::class, 'executeOperation'])->name('operation.execute');
    Route::post('/operation/{operation}/validate', [OperationController::class, 'validate'])->name('operation.validate');
    Route::post('/operation/{operation}/reject', [OperationController::class, 'reject'])->name('operation.reject');

    Route::resource('parametre', ParametreController::class);
    
    // Routes de gestion des utilisateurs
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::resource('user', UserController::class);
    });



});

require __DIR__.'/auth.php';
