<?php

use App\Http\Controllers\CashAccountController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::resource('cash-account', CashAccountController::class)
    ->parameters([
        'cash-account' => 'uuid',
    ]);

require __DIR__.'/settings.php';
