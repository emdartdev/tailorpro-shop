<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RunningYearController;
use App\Http\Controllers\Api\StaffPaymentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkerController;
use App\Http\Controllers\Api\WorkerEntryController;
use Illuminate\Support\Facades\Route;

// Public Auth Routes (Rate Limited to prevent brute-force attacks)
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
});

// Authenticated Routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Current User Profile & Sign Out
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::match(['post', 'put'], '/auth/change-password', [AuthController::class, 'changePassword']);
    Route::match(['post', 'put'], '/auth/password', [AuthController::class, 'changePassword']);

    // Read-only accessible by viewer, editor, and admin
    Route::get('/workers', [WorkerController::class, 'index']);
    Route::get('/workers/{id}', [WorkerController::class, 'show']);
    Route::get('/worker-entries', [WorkerEntryController::class, 'index']);
    Route::get('/staff-payments', [StaffPaymentController::class, 'index']);
    Route::get('/running-years', [RunningYearController::class, 'index']);
    Route::get('/catalogs', [CatalogController::class, 'index']);
    Route::get('/catalogs/officer', [CatalogController::class, 'getOfficer']);
    Route::get('/catalogs/airman', [CatalogController::class, 'getAirman']);
    Route::get('/catalogs/worker', [CatalogController::class, 'getWorker']);

    // Operations on catalogs & orders (restricted to admin & editor)
    Route::middleware(['role:admin,editor'])->group(function () {
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::get('/customers/{id}', [CustomerController::class, 'show']);
        Route::get('/customers/{id}/history', [CustomerController::class, 'history']);
        Route::post('/customers', [CustomerController::class, 'store']);
        Route::put('/customers/{id}', [CustomerController::class, 'update']);
        Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/next-number', [OrderController::class, 'nextNumber']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::put('/orders/{id}', [OrderController::class, 'update']);
        Route::post('/orders/{id}/deliver', [OrderController::class, 'deliver']);
        Route::delete('/orders/{id}', [OrderController::class, 'destroy']);

        Route::post('/workers', [WorkerController::class, 'store']);
        Route::put('/workers/{id}', [WorkerController::class, 'update']);
        Route::delete('/workers/{id}', [WorkerController::class, 'destroy']);
        Route::post('/workers/{id}/restore', [WorkerController::class, 'restore']);

        Route::post('/worker-entries', [WorkerEntryController::class, 'store']);
        Route::post('/worker-entries/bulk-upsert', [WorkerEntryController::class, 'bulkUpsert']);
        Route::put('/worker-entries/{id}', [WorkerEntryController::class, 'update']);
        Route::delete('/worker-entries/{id}', [WorkerEntryController::class, 'destroy']);

        Route::post('/staff-payments', [StaffPaymentController::class, 'store']);
        Route::delete('/staff-payments/{id}', [StaffPaymentController::class, 'destroy']);

        Route::post('/catalogs', [CatalogController::class, 'store']);
        Route::put('/catalogs/{id}', [CatalogController::class, 'update']);
        Route::delete('/catalogs/{id}', [CatalogController::class, 'destroy']);
        Route::post('/catalogs/officer', [CatalogController::class, 'storeOfficer']);
        Route::post('/catalogs/airman', [CatalogController::class, 'storeAirman']);
        Route::post('/catalogs/worker', [CatalogController::class, 'storeWorker']);

        Route::post('/running-years', [RunningYearController::class, 'store']);
        Route::put('/running-years/{id}', [RunningYearController::class, 'update']);
        Route::delete('/running-years/{id}', [RunningYearController::class, 'destroy']);

        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/results', [DashboardController::class, 'results']);
        Route::get('/dashboard/metrics', [DashboardController::class, 'metrics']);
        Route::get('/users', [UserController::class, 'index']);
    });

    // Admin-Only Routes
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/users', [UserController::class, 'invite']);
        Route::post('/users/invite', [UserController::class, 'invite']);
        Route::put('/users/{id}/role', [UserController::class, 'updateRole']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
    });
});
