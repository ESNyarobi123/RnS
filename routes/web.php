<?php

use App\Http\Controllers\QrRedirectController;
use App\Http\Controllers\DashboardRedirectController;
use App\Livewire\Admin\Businesses as AdminBusinesses;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Feedbacks;
use App\Livewire\Admin\BotControl;
use App\Livewire\Admin\Managers as AdminManagers;
use App\Livewire\Admin\Orders as AdminOrders;
use App\Livewire\Admin\Payments as AdminPayments;
use App\Livewire\Admin\Payrolls as AdminPayrolls;
use App\Livewire\Admin\Workers as AdminWorkers;
use App\Livewire\Manager\BusinessSettings;
use App\Livewire\Manager\CreateBusiness;
use App\Livewire\Manager\Dashboard as ManagerDashboard;
use App\Livewire\Manager\LiveOrders;
use App\Livewire\Manager\ManageMenu;
use App\Livewire\Manager\ManageOrders;
use App\Livewire\Manager\ManagePayrolls;
use App\Livewire\Manager\ManageProducts;
use App\Livewire\Manager\ManageTables;
use App\Livewire\Manager\ManageWorkers;
use App\Livewire\Manager\ManageFeedbacks;
use App\Livewire\Manager\ManagerTips;
use App\Livewire\Manager\PaymentSettings;
use App\Livewire\Manager\CustomerCalls;
use App\Livewire\Manager\WorkerPerformance;
use App\Livewire\Manager\ShiftManagement;
use App\Livewire\Worker\Dashboard as WorkerDashboard;
use App\Livewire\Worker\CustomerCalls as WorkerCustomerCalls;
use App\Livewire\Worker\MyOrders as WorkerMyOrders;
use App\Livewire\Worker\MyPayrolls as WorkerMyPayrolls;
use App\Livewire\Worker\MyReviews as WorkerMyReviews;
use App\Livewire\Worker\MyTips as WorkerMyTips;
use App\Livewire\Worker\LiveOrders as WorkerLiveOrders;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::post('/webhooks/selcom', \App\Http\Controllers\SelcomWebhookController::class)
    ->name('webhooks.selcom')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/webhooks/whatsapp', \App\Http\Controllers\WhatsAppWebhookController::class)
    ->name('webhooks.whatsapp')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/qr/{code}', QrRedirectController::class)->name('qr.scan');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardRedirectController::class)->name('dashboard');

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', AdminDashboard::class)->name('dashboard');
        Route::get('businesses', AdminBusinesses::class)->name('businesses');
        Route::get('managers', AdminManagers::class)->name('managers');
        Route::get('workers', AdminWorkers::class)->name('workers');
        Route::get('orders', AdminOrders::class)->name('orders');
        Route::get('payments', AdminPayments::class)->name('payments');
        Route::get('payrolls', AdminPayrolls::class)->name('payrolls');
        Route::get('feedbacks', Feedbacks::class)->name('feedbacks');
        Route::get('bot-control', BotControl::class)->name('bot-control');
    });

    // Manager routes
    Route::middleware('role:manager')->prefix('manager')->name('manager.')->group(function () {
        Route::get('dashboard', ManagerDashboard::class)->name('dashboard');
        Route::get('business/create', CreateBusiness::class)->name('business.create');
        Route::get('workers', ManageWorkers::class)->name('workers.index');
        Route::get('tables', ManageTables::class)->name('tables.index');
        Route::get('menu', ManageMenu::class)->name('menu.index');
        Route::get('tips', ManagerTips::class)->name('tips.index');
        Route::get('feedbacks', ManageFeedbacks::class)->name('feedbacks.index');
        Route::get('customer-calls', CustomerCalls::class)->name('customer-calls.index');
        Route::get('worker-performance', WorkerPerformance::class)->name('worker-performance.index');
        Route::get('shift-management', ShiftManagement::class)->name('shift-management.index');
        Route::get('products', ManageProducts::class)->name('products.index');
        Route::get('orders', ManageOrders::class)->name('orders.index');
        Route::get('payrolls', ManagePayrolls::class)->name('payrolls.index');
        Route::get('live-orders', LiveOrders::class)->name('live-orders');
        Route::get('payment-settings', PaymentSettings::class)->name('payment-settings');
        Route::get('settings', BusinessSettings::class)->name('settings');
    });

    // Worker routes
    Route::middleware('role:worker')->prefix('worker')->name('worker.')->group(function () {
        Route::get('dashboard', WorkerDashboard::class)->name('dashboard');
        Route::get('orders', WorkerMyOrders::class)->name('orders');
        Route::get('payrolls', WorkerMyPayrolls::class)->name('payrolls');
        Route::get('reviews', WorkerMyReviews::class)->name('reviews');
        Route::get('tips', WorkerMyTips::class)->name('tips');
        Route::get('customer-calls', WorkerCustomerCalls::class)->name('customer-calls');
        Route::get('live-orders', WorkerLiveOrders::class)->name('live-orders');
    });
});

require __DIR__.'/settings.php';
