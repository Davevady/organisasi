<?php

use App\Http\Controllers\CashAccountController;
use App\Http\Controllers\CashTransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('cash-account', CashAccountController::class)
    ->parameters([
        'cash-account' => 'uuid',
    ]);

Route::get('cash-transaction/summary', [CashTransactionController::class, 'summary'])->name('cash-transaction.summary');
Route::get('cash-transaction/trashed', [CashTransactionController::class, 'trashed'])->name('cash-transaction.trashed');
Route::post('cash-transaction/{uuid}/restore', [CashTransactionController::class, 'restore'])->name('cash-transaction.restore');
Route::delete('cash-transaction/{uuid}/force-delete', [CashTransactionController::class, 'forceDelete'])->name('cash-transaction.force-delete');
Route::resource('cash-transaction', CashTransactionController::class)
    ->parameters([
        'cash-transaction' => 'uuid',
    ]);

require __DIR__.'/settings.php';
