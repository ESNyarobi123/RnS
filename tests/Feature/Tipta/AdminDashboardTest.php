<?php

use App\Enums\BusinessStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PayrollStatus;
use App\Livewire\Admin\Businesses as AdminBusinesses;
use App\Livewire\Admin\Feedbacks as AdminFeedbacks;
use App\Livewire\Admin\Orders as AdminOrders;
use App\Livewire\Admin\Payments as AdminPayments;
use App\Livewire\Admin\Payrolls as AdminPayrolls;
use App\Models\Business;
use App\Models\BusinessWorker;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\User;
use Livewire\Livewire;

// === Route Access Control ===

test('admin can access admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertOk();
});

test('manager cannot access admin dashboard', function () {
    $manager = User::factory()->manager()->create();

    $this->actingAs($manager)
        ->get('/admin/dashboard')
        ->assertStatus(403);
});

test('worker cannot access admin dashboard', function () {
    $worker = User::factory()->worker()->create();

    $this->actingAs($worker)
        ->get('/admin/dashboard')
        ->assertStatus(403);
});

test('admin can access businesses page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/businesses')
        ->assertOk();
});

test('admin can access managers page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/managers')
        ->assertOk();
});

test('admin can access workers page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/workers')
        ->assertOk();
});

test('admin can access orders page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/orders')
        ->assertOk();
});

test('admin can access payments page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/payments')
        ->assertOk();
});

test('admin can access payrolls page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/payrolls')
        ->assertOk();
});

// === Dashboard Shows Stats ===

test('admin dashboard shows platform statistics', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $worker = User::factory()->worker()->create();
    $business = Business::factory()->restaurant()->create(['user_id' => $manager->id]);

    BusinessWorker::factory()->create([
        'business_id' => $business->id,
        'worker_id' => $worker->id,
    ]);

    Order::factory()->create(['business_id' => $business->id, 'worker_id' => $worker->id]);
    Payment::factory()->create(['business_id' => $business->id, 'status' => PaymentStatus::Completed]);

    $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertOk()
        ->assertSeeText('Admin Dashboard')
        ->assertSeeText('Businesses')
        ->assertSeeText('Orders')
        ->assertSeeText('Revenue');
});

// === Businesses Page ===

test('admin businesses page shows all businesses', function () {
    $admin = User::factory()->admin()->create();
    $restaurant = Business::factory()->restaurant()->create(['name' => 'Test Restaurant']);
    $salon = Business::factory()->salon()->create(['name' => 'Test Salon']);

    $this->actingAs($admin)
        ->get('/admin/businesses')
        ->assertOk()
        ->assertSeeText('Test Restaurant')
        ->assertSeeText('Test Salon');
});

test('admin businesses page filters by type', function () {
    $admin = User::factory()->admin()->create();
    Business::factory()->restaurant()->create(['name' => 'My Restaurant']);
    Business::factory()->salon()->create(['name' => 'My Salon']);

    $this->actingAs($admin)
        ->get('/admin/businesses?type=restaurant')
        ->assertOk()
        ->assertSeeText('My Restaurant');
});

// === Orders Page ===

test('admin orders page shows all orders', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create();

    $this->actingAs($admin)
        ->get('/admin/orders')
        ->assertOk()
        ->assertSeeText($order->order_number);
});

test('admin orders page filters by status', function () {
    $admin = User::factory()->admin()->create();
    Order::factory()->create(['status' => OrderStatus::Pending]);
    Order::factory()->completed()->create();

    $this->actingAs($admin)
        ->get('/admin/orders?status=pending')
        ->assertOk();
});

// === Payments Page ===

test('admin payments page shows all payments', function () {
    $admin = User::factory()->admin()->create();
    Payment::factory()->create();

    $this->actingAs($admin)
        ->get('/admin/payments')
        ->assertOk()
        ->assertSeeText('All Payments');
});

// === Payrolls Page ===

test('admin payrolls page shows all payrolls', function () {
    $admin = User::factory()->admin()->create();
    Payroll::factory()->create();

    $this->actingAs($admin)
        ->get('/admin/payrolls')
        ->assertOk()
        ->assertSeeText('All Payrolls');
});

// === Workers Page ===

test('admin workers page shows all workers', function () {
    $admin = User::factory()->admin()->create();
    $worker = User::factory()->worker()->create(['name' => 'John Doe Worker']);

    $this->actingAs($admin)
        ->get('/admin/workers')
        ->assertOk()
        ->assertSeeText('John Doe Worker')
        ->assertSeeText($worker->global_number);
});

// === Managers Page ===

test('admin managers page shows all managers', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create(['name' => 'Jane Manager']);

    $this->actingAs($admin)
        ->get('/admin/managers')
        ->assertOk()
        ->assertSeeText('Jane Manager');
});

// === Non-admin access blocked for all admin routes ===

test('non-admin cannot access any admin route', function () {
    $worker = User::factory()->worker()->create();

    $routes = ['/admin/businesses', '/admin/managers', '/admin/workers', '/admin/orders', '/admin/payments', '/admin/payrolls', '/admin/feedbacks'];

    foreach ($routes as $route) {
        $this->actingAs($worker)
            ->get($route)
            ->assertStatus(403);
    }
});

// === Admin Feedback Page ===

test('admin can access feedbacks page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/feedbacks')
        ->assertOk()
        ->assertSeeText('Feedback & Reviews');
});

test('admin feedbacks page shows feedback data', function () {
    $admin = User::factory()->admin()->create();
    Feedback::factory()->create(['customer_name' => 'Test Customer', 'rating' => 5]);

    $this->actingAs($admin)
        ->get('/admin/feedbacks')
        ->assertOk()
        ->assertSeeText('Test Customer');
});

// === Order Status Change Actions ===

test('admin can update order status to confirmed', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create(['status' => OrderStatus::Pending]);

    Livewire::actingAs($admin)
        ->test(AdminOrders::class)
        ->call('updateOrderStatus', $order->id, 'confirmed');

    expect($order->fresh()->status)->toBe(OrderStatus::Confirmed);
});

test('admin can mark order as completed', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create(['status' => OrderStatus::InProgress]);

    Livewire::actingAs($admin)
        ->test(AdminOrders::class)
        ->call('updateOrderStatus', $order->id, 'completed');

    expect($order->fresh()->status)->toBe(OrderStatus::Completed)
        ->and($order->fresh()->completed_at)->not->toBeNull();
});

test('admin can cancel order', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create(['status' => OrderStatus::Pending]);

    Livewire::actingAs($admin)
        ->test(AdminOrders::class)
        ->call('updateOrderStatus', $order->id, 'cancelled');

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled);
});

// === Payment Actions ===

test('admin can mark payment as completed', function () {
    $admin = User::factory()->admin()->create();
    $payment = Payment::factory()->create(['status' => PaymentStatus::Pending, 'paid_at' => null]);

    Livewire::actingAs($admin)
        ->test(AdminPayments::class)
        ->call('markPaymentCompleted', $payment->id);

    expect($payment->fresh()->status)->toBe(PaymentStatus::Completed)
        ->and($payment->fresh()->paid_at)->not->toBeNull();
});

test('admin can mark payment as failed', function () {
    $admin = User::factory()->admin()->create();
    $payment = Payment::factory()->create(['status' => PaymentStatus::Pending]);

    Livewire::actingAs($admin)
        ->test(AdminPayments::class)
        ->call('updatePaymentStatus', $payment->id, 'failed');

    expect($payment->fresh()->status)->toBe(PaymentStatus::Failed);
});

test('admin can refund payment', function () {
    $admin = User::factory()->admin()->create();
    $payment = Payment::factory()->create(['status' => PaymentStatus::Completed]);

    Livewire::actingAs($admin)
        ->test(AdminPayments::class)
        ->call('updatePaymentStatus', $payment->id, 'refunded');

    expect($payment->fresh()->status)->toBe(PaymentStatus::Refunded);
});

// === Payroll Actions ===

test('admin can mark payroll as paid', function () {
    $admin = User::factory()->admin()->create();
    $payroll = Payroll::factory()->create(['status' => PayrollStatus::Pending, 'paid_at' => null]);

    Livewire::actingAs($admin)
        ->test(AdminPayrolls::class)
        ->call('markPayrollPaid', $payroll->id);

    expect($payroll->fresh()->status)->toBe(PayrollStatus::Paid)
        ->and($payroll->fresh()->paid_at)->not->toBeNull();
});

// === Business Status Toggle ===

test('admin can activate a business', function () {
    $admin = User::factory()->admin()->create();
    $business = Business::factory()->create(['status' => BusinessStatus::Inactive]);

    Livewire::actingAs($admin)
        ->test(AdminBusinesses::class)
        ->call('toggleStatus', $business->id, 'active');

    expect($business->fresh()->status)->toBe(BusinessStatus::Active);
});

test('admin can suspend a business', function () {
    $admin = User::factory()->admin()->create();
    $business = Business::factory()->create(['status' => BusinessStatus::Active]);

    Livewire::actingAs($admin)
        ->test(AdminBusinesses::class)
        ->call('toggleStatus', $business->id, 'suspended');

    expect($business->fresh()->status)->toBe(BusinessStatus::Suspended);
});

test('admin can deactivate a business', function () {
    $admin = User::factory()->admin()->create();
    $business = Business::factory()->create(['status' => BusinessStatus::Active]);

    Livewire::actingAs($admin)
        ->test(AdminBusinesses::class)
        ->call('toggleStatus', $business->id, 'inactive');

    expect($business->fresh()->status)->toBe(BusinessStatus::Inactive);
});

// === Feedback Delete Action ===

test('admin can delete feedback', function () {
    $admin = User::factory()->admin()->create();
    $feedback = Feedback::factory()->create();

    Livewire::actingAs($admin)
        ->test(AdminFeedbacks::class)
        ->call('deleteFeedback', $feedback->id);

    expect(Feedback::find($feedback->id))->toBeNull();
});

// === Order View Detail ===

test('admin can open order detail modal', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create();

    Livewire::actingAs($admin)
        ->test(AdminOrders::class)
        ->call('viewOrder', $order->id)
        ->assertSet('showDetailModal', true)
        ->assertSet('selectedOrderId', $order->id);
});

// === Business View Detail ===

test('admin can open business detail modal', function () {
    $admin = User::factory()->admin()->create();
    $business = Business::factory()->create();

    Livewire::actingAs($admin)
        ->test(AdminBusinesses::class)
        ->call('viewBusiness', $business->id)
        ->assertSet('showDetailModal', true)
        ->assertSet('selectedBusinessId', $business->id);
});
