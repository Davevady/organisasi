<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AuthController, CashAccountController, CashTransactionController, CategoryController, InventoryItemController, InventoryTransactionController, MemberController, MemberPaymentController, UnitController};

// Public routes (no authentication required)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes (authentication required)
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth endpoints
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('logout-all', [AuthController::class, 'logoutAll']);
});

// Admin & Super Admin routes
Route::middleware(['auth:sanctum', 'role:user,admin,super-admin'])->group(function () {

    // Inventory Items
    Route::prefix('inventory')->group(function () {
        Route::get('items', [InventoryItemController::class, 'index']);
        Route::post('items', [InventoryItemController::class, 'store']);
        Route::get('items/low-stock', [InventoryItemController::class, 'lowStock']);
        Route::get('items/out-of-stock', [InventoryItemController::class, 'outOfStock']);
        Route::get('items/{inventoryItem}', [InventoryItemController::class, 'show']);
        Route::put('items/{inventoryItem}', [InventoryItemController::class, 'update']);
        Route::delete('items/{inventoryItem}', [InventoryItemController::class, 'destroy']);

        // Inventory Transactions
        Route::get('transactions', [InventoryTransactionController::class, 'index']);
        Route::post('transactions', [InventoryTransactionController::class, 'store']);
        Route::get('transactions/{inventoryTransaction}', [InventoryTransactionController::class, 'show']);
        Route::get('items/{inventoryItem}/history', [InventoryTransactionController::class, 'history']);
    });

    // Members
    Route::prefix('members')->group(function () {
        Route::get('/', [MemberController::class, 'index']);
        Route::post('/', [MemberController::class, 'store']);
        Route::get('/active', [MemberController::class, 'active']);
        Route::get('/{member}', [MemberController::class, 'show']);
        Route::put('/{member}', [MemberController::class, 'update']);
        Route::delete('/{member}', [MemberController::class, 'destroy']);
        Route::get('/{member}/payments', [MemberController::class, 'paymentHistory']);
    });

    // Member Payments
    Route::prefix('payments')->group(function () {
        Route::get('/', [MemberPaymentController::class, 'index']);
        Route::post('/', [MemberPaymentController::class, 'store']);
        Route::get('/summary', [MemberPaymentController::class, 'summary']);
        Route::get('/unpaid/{period}', [MemberPaymentController::class, 'unpaidByPeriod']);
        Route::get('/{memberPayment}', [MemberPaymentController::class, 'show']);
        Route::put('/{memberPayment}', [MemberPaymentController::class, 'update']);
    });

    // Cash Management
    Route::prefix('cash')->group(function () {
        // Cash Accounts
        Route::get('accounts', [CashAccountController::class, 'index']);
        Route::post('accounts', [CashAccountController::class, 'store']);
        Route::get('accounts/{cashAccount}', [CashAccountController::class, 'show']);
        Route::put('accounts/{cashAccount}', [CashAccountController::class, 'update']);
        Route::delete('accounts/{cashAccount}', [CashAccountController::class, 'destroy']);

        // Cash Transactions
        Route::get('transactions', [CashTransactionController::class, 'index']);
        Route::post('transactions', [CashTransactionController::class, 'store']);
        Route::get('transactions/summary', [CashTransactionController::class, 'summary']);
        Route::get('transactions/by-category', [CashTransactionController::class, 'byCategory']);
        Route::get('transactions/{cashTransaction}', [CashTransactionController::class, 'show']);
    });

    // Master Data - Categories
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('/{category}', [CategoryController::class, 'show']);
        Route::put('/{category}', [CategoryController::class, 'update']);
        Route::delete('/{category}', [CategoryController::class, 'destroy']);
    });

    // Master Data - Units
    Route::prefix('units')->group(function () {
        Route::get('/', [UnitController::class, 'index']);
        Route::post('/', [UnitController::class, 'store']);
        Route::get('/{unit}', [UnitController::class, 'show']);
        Route::put('/{unit}', [UnitController::class, 'update']);
        Route::delete('/{unit}', [UnitController::class, 'destroy']);
    });
});
