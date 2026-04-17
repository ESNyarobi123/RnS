<?php

use App\Enums\PayrollStatus;
use App\Livewire\Manager\ManagePayrolls;
use App\Models\Business;
use App\Models\BusinessWorker;
use App\Models\Payroll;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->manager = User::factory()->manager()->create();
    $this->business = Business::factory()->restaurant()->create(['user_id' => $this->manager->id]);
    $this->worker = User::factory()->worker()->create();
    BusinessWorker::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
    ]);
});

test('manager can view payrolls page', function () {
    Livewire::actingAs($this->manager)
        ->test(ManagePayrolls::class)
        ->assertOk()
        ->assertSee('Payrolls');
});

test('manager sees their business payrolls', function () {
    $payroll = Payroll::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
    ]);
    $otherPayroll = Payroll::factory()->create();

    Livewire::actingAs($this->manager)
        ->test(ManagePayrolls::class)
        ->assertSee($this->worker->name)
        ->assertDontSee($otherPayroll->worker->name);
});

test('manager can create a payroll', function () {
    Livewire::actingAs($this->manager)
        ->test(ManagePayrolls::class)
        ->call('openCreatePayroll')
        ->set('payrollWorkerId', $this->worker->id)
        ->set('payrollAmount', '150000')
        ->set('payrollPeriodStart', '2026-04-01')
        ->set('payrollPeriodEnd', '2026-04-30')
        ->set('payrollNotes', 'April salary')
        ->call('savePayroll');

    expect($this->business->payrolls)->toHaveCount(1)
        ->and($this->business->payrolls->first()->worker_id)->toBe($this->worker->id)
        ->and((float) $this->business->payrolls->first()->amount)->toBe(150000.0)
        ->and($this->business->payrolls->first()->status)->toBe(PayrollStatus::Pending);
});

test('manager can mark payroll as paid', function () {
    $payroll = Payroll::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
    ]);

    Livewire::actingAs($this->manager)
        ->test(ManagePayrolls::class)
        ->call('markPaid', $payroll->id);

    expect($payroll->fresh()->status)->toBe(PayrollStatus::Paid)
        ->and($payroll->fresh()->paid_at)->not->toBeNull();
});

test('payroll creation requires worker and amount', function () {
    Livewire::actingAs($this->manager)
        ->test(ManagePayrolls::class)
        ->call('openCreatePayroll')
        ->set('payrollWorkerId', null)
        ->set('payrollAmount', '')
        ->set('payrollPeriodStart', '')
        ->set('payrollPeriodEnd', '')
        ->call('savePayroll')
        ->assertHasErrors(['payrollWorkerId', 'payrollAmount', 'payrollPeriodStart', 'payrollPeriodEnd']);
});

test('manager can search payrolls by worker name', function () {
    $worker2 = User::factory()->worker()->create(['name' => 'Uniquexyz Searchtest']);
    BusinessWorker::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $worker2->id,
    ]);
    Payroll::factory()->create(['business_id' => $this->business->id, 'worker_id' => $this->worker->id]);
    Payroll::factory()->create(['business_id' => $this->business->id, 'worker_id' => $worker2->id]);

    Livewire::actingAs($this->manager)
        ->test(ManagePayrolls::class)
        ->set('search', 'Uniquexyz')
        ->assertSee('Uniquexyz Searchtest');
});

test('payroll summary shows correct totals', function () {
    Payroll::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'amount' => 100000,
        'status' => PayrollStatus::Paid,
        'paid_at' => now(),
    ]);
    Payroll::factory()->create([
        'business_id' => $this->business->id,
        'worker_id' => $this->worker->id,
        'amount' => 50000,
        'status' => PayrollStatus::Pending,
    ]);

    $component = Livewire::actingAs($this->manager)->test(ManagePayrolls::class);

    expect($component->get('payrollSummary')['total_paid'])->toBe(100000.0)
        ->and($component->get('payrollSummary')['total_pending'])->toBe(50000.0)
        ->and($component->get('payrollSummary')['paid_count'])->toBe(1)
        ->and($component->get('payrollSummary')['pending_count'])->toBe(1);
});
