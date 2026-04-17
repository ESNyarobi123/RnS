<?php

use App\Http\Controllers\Api\BotController;
use Illuminate\Support\Facades\Route;

// Bot API Routes
Route::prefix('bot')->middleware(['throttle:60,1'])->group(function () {
    
    // Health check
    Route::get('/health', [BotController::class, 'health'])->name('bot.health');
    
    // Business endpoints
    Route::get('/business/{code}', [BotController::class, 'getBusinessByCode']);
    Route::get('/business/{id}/menu', [BotController::class, 'getBusinessMenu']);
    Route::get('/business/{id}/tables', [BotController::class, 'getBusinessTables']);
    Route::get('/business/{id}/workers', [BotController::class, 'getBusinessWorkers']);
    
    // Worker endpoints
    Route::get('/worker/{code}', [BotController::class, 'getWorkerByCode']);
    Route::get('/worker/{id}/tips', [BotController::class, 'getWorkerTips']);
    Route::get('/worker/{id}/feedbacks', [BotController::class, 'getWorkerFeedbacks']);
    Route::get('/worker/{id}/customers', [BotController::class, 'getWorkerCustomersServed']);
    
    // Table endpoints
    Route::get('/table/{code}', [BotController::class, 'getTableByCode']);
    
    // Order endpoints
    Route::post('/orders', [BotController::class, 'createOrder']);
    Route::get('/orders/{id}', [BotController::class, 'getOrderStatus']);
    
    // Payment endpoints
    Route::post('/payments/initiate', [BotController::class, 'initiatePayment']);
    Route::get('/payments/{id}/status', [BotController::class, 'checkPaymentStatus']);
    
    // Service endpoints
    Route::post('/feedbacks', [BotController::class, 'submitFeedback']);
    Route::post('/tips', [BotController::class, 'submitTip']);
    Route::post('/call-waiter', [BotController::class, 'callWaiter']);
    Route::post('/support', [BotController::class, 'createSupportTicket']);
});
