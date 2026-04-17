<?php

use App\Enums\OrderStatus;
use App\Enums\PayrollStatus;
use App\Enums\UserRole;
use App\Livewire\Worker\CustomerCalls;
use App\Livewire\Worker\MyOrders;
use App\Livewire\Worker\MyPayrolls;
use App\Livewire\Worker\MyReviews;
use App\Livewire\Worker\MyTips;
use App\Models\Business;
use App\Models\BusinessWorker;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\Payroll;
use App\Models\Table;
use App\Models\Tip;
use App\Models\User;
use App\Models\WaiterCall;
use Livewire\Livewire;

beforeEach(function () {
    $this->worker = User::factory()->worker()->create();
    $this->business = Business::factory()->restaurant()->create();
    BusinessWorker::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'is_active' => true,
    ]);
});

// === My Orders ===

test('worker can view my orders page', function () {
    Livewire::actingAs($this->worker)
        ->test(MyOrders::class)
        ->assertOk()
        ->assertSee('My Orders');
});

test('worker sees assigned orders', function () {
    $order = Order::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'customer_name' => 'John Test',
    ]);

    Livewire::actingAs($this->worker)
        ->test(MyOrders::class)
        ->assertSee($order->order_number)
        ->assertSee('John Test');
});

test('worker does not see other worker orders', function () {
    $otherWorker = User::factory()->worker()->create();
    $order = Order::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $otherWorker->id,
        'customer_name' => 'Secret Customer',
    ]);

    Livewire::actingAs($this->worker)
        ->test(MyOrders::class)
        ->assertDontSee('Secret Customer');
});

test('worker can filter orders by status', function () {
    Order::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'status' => OrderStatus::Completed,
        'customer_name' => 'Done Customer',
    ]);

    Order::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'status' => OrderStatus::Pending,
        'customer_name' => 'Waiting Customer',
    ]);

    Livewire::actingAs($this->worker)
        ->test(MyOrders::class)
        ->set('status', 'completed')
        ->assertSee('Done Customer')
        ->assertDontSee('Waiting Customer');
});

test('worker can search orders', function () {
    Order::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'customer_name' => 'Alice Unique',
    ]);

    Order::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'customer_name' => 'Bob Normal',
    ]);

    Livewire::actingAs($this->worker)
        ->test(MyOrders::class)
        ->set('search', 'Alice')
        ->assertSee('Alice Unique')
        ->assertDontSee('Bob Normal');
});

test('worker orders page shows status counts', function () {
    Order::factory()->count(2)->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'status' => OrderStatus::Pending,
    ]);

    $component = Livewire::actingAs($this->worker)
        ->test(MyOrders::class);

    expect($component->get('statusCounts')['pending'])->toBe(2);
});

// === My Payrolls ===

test('worker can view my payrolls page', function () {
    Livewire::actingAs($this->worker)
        ->test(MyPayrolls::class)
        ->assertOk()
        ->assertSee('My Payrolls');
});

test('worker sees payroll records', function () {
    Payroll::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'amount' => 50000,
        'status' => PayrollStatus::Paid,
    ]);

    Livewire::actingAs($this->worker)
        ->test(MyPayrolls::class)
        ->assertSee('50,000');
});

test('worker payrolls summary shows correct totals', function () {
    Payroll::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'amount' => 30000,
        'status' => PayrollStatus::Paid,
    ]);

    Payroll::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'amount' => 10000,
        'status' => PayrollStatus::Pending,
    ]);

    $component = Livewire::actingAs($this->worker)
        ->test(MyPayrolls::class);

    expect($component->get('summary')['total_earned'])->toBe(30000.0)
        ->and($component->get('summary')['pending'])->toBe(10000.0)
        ->and($component->get('summary')['paid_count'])->toBe(1)
        ->and($component->get('summary')['pending_count'])->toBe(1);
});

test('worker can filter payrolls by status', function () {
    Payroll::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'amount' => 80000,
        'status' => PayrollStatus::Paid,
        'notes' => 'paid-marker',
    ]);

    Payroll::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'amount' => 20000,
        'status' => PayrollStatus::Pending,
        'notes' => 'pending-marker',
    ]);

    Livewire::actingAs($this->worker)
        ->test(MyPayrolls::class)
        ->set('status', 'paid')
        ->assertSee('paid-marker')
        ->assertDontSee('pending-marker');
});

// === My Reviews ===

test('worker can view my reviews page', function () {
    Livewire::actingAs($this->worker)
        ->test(MyReviews::class)
        ->assertOk()
        ->assertSee('My Reviews');
});

test('worker sees feedback reviews', function () {
    Feedback::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'customer_name' => 'Happy Customer',
        'rating' => 5,
        'comment' => 'Excellent service!',
    ]);

    Livewire::actingAs($this->worker)
        ->test(MyReviews::class)
        ->assertSee('Happy Customer')
        ->assertSee('Excellent service!');
});

test('worker reviews summary shows correct breakdown', function () {
    Feedback::factory()->count(3)->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'rating' => 5,
    ]);

    Feedback::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'rating' => 3,
    ]);

    $component = Livewire::actingAs($this->worker)
        ->test(MyReviews::class);

    $summary = $component->get('summary');
    expect($summary['total'])->toBe(4)
        ->and($summary['avg'])->toBe(4.5)
        ->and($summary['breakdown'][5]['count'])->toBe(3)
        ->and($summary['breakdown'][3]['count'])->toBe(1);
});

// === My Tips ===

test('worker can view my tips page', function () {
    Livewire::actingAs($this->worker)
        ->test(MyTips::class)
        ->assertOk()
        ->assertSee('My Tips');
});

test('worker sees only own business tips', function () {
    Tip::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'customer_name' => 'Tip Customer',
        'amount' => 15000,
    ]);

    $otherBusiness = Business::factory()->restaurant()->create();
    Tip::factory()->create([
        'business_id' => $otherBusiness->id,
        'worker_id' => $this->worker->id,
        'customer_name' => 'Hidden Customer',
        'amount' => 25000,
    ]);

    Livewire::actingAs($this->worker)
        ->test(MyTips::class)
        ->assertSee('Tip Customer')
        ->assertDontSee('Hidden Customer');
});

// === Customer Calls ===

test('worker can view customer calls page', function () {
    Livewire::actingAs($this->worker)
        ->test(CustomerCalls::class)
        ->assertOk()
        ->assertSee('Customer Calls');
});

test('worker sees whatsapp customer calls for linked business', function () {
    $table = Table::query()->create([
        'business_id' => $this->business->id,
        'name' => '1',
        'capacity' => 4,
        'status' => 'available',
    ]);

    WaiterCall::factory()->create([
        'business_id' => $this->business->id,
        'table_id' => $table->id,
        'customer_name' => 'Amina Caller',
        'notes' => 'Need quick help',
    ]);

    Livewire::actingAs($this->worker)
        ->test(CustomerCalls::class)
        ->assertSee('Amina Caller')
        ->assertSee('Need quick help');
});

test('worker can filter reviews by rating', function () {
    Feedback::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'customer_name' => 'Five Star',
        'rating' => 5,
    ]);

    Feedback::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'customer_name' => 'Three Star',
        'rating' => 3,
    ]);

    Livewire::actingAs($this->worker)
        ->test(MyReviews::class)
        ->set('rating', '5')
        ->assertSee('Five Star')
        ->assertDontSee('Three Star');
});

test('worker does not see other workers feedback', function () {
    $otherWorker = User::factory()->worker()->create();
    Feedback::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $otherWorker->id,
        'customer_name' => 'Not My Review',
        'rating' => 4,
    ]);

    Livewire::actingAs($this->worker)
        ->test(MyReviews::class)
        ->assertDontSee('Not My Review');
});

// === Unlinked Worker ===

test('unlinked worker sees empty state on orders', function () {
    $unlinked = User::factory()->worker()->create();

    Livewire::actingAs($unlinked)
        ->test(MyOrders::class)
        ->assertSee('linked to a business');
});

test('unlinked worker sees empty state on payrolls', function () {
    $unlinked = User::factory()->worker()->create();

    Livewire::actingAs($unlinked)
        ->test(MyPayrolls::class)
        ->assertSee('linked to a business');
});

test('unlinked worker sees empty state on reviews', function () {
    $unlinked = User::factory()->worker()->create();

    Livewire::actingAs($unlinked)
        ->test(MyReviews::class)
        ->assertSee('linked to a business');
});

// === Route Access ===

test('worker can access orders route', function () {
    $this->actingAs($this->worker)
        ->get(route('worker.orders'))
        ->assertOk();
});

test('worker can access payrolls route', function () {
    $this->actingAs($this->worker)
        ->get(route('worker.payrolls'))
        ->assertOk();
});

test('worker can access reviews route', function () {
    $this->actingAs($this->worker)
        ->get(route('worker.reviews'))
        ->assertOk();
});

test('worker can access customer calls route', function () {
    $this->actingAs($this->worker)
        ->get(route('worker.customer-calls'))
        ->assertOk();
});

test('non-worker cannot access worker routes', function () {
    $manager = User::factory()->manager()->create();

    $this->actingAs($manager)
        ->get(route('worker.orders'))
        ->assertForbidden();
});
