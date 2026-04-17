<?php

use App\Livewire\Manager\ManageTables;
use App\Models\Business;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->manager = User::factory()->manager()->create();
    Business::factory()->restaurant()->create(['user_id' => $this->manager->id]);
});

test('manager can view tables page', function () {
    Livewire::actingAs($this->manager)
        ->test(ManageTables::class)
        ->assertOk();
});

test('add table opens modal', function () {
    Livewire::actingAs($this->manager)
        ->test(ManageTables::class)
        ->call('create')
        ->assertSet('showTableModal', true);
});
