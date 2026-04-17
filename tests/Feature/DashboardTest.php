<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users are redirected to their role dashboard', function () {
    $worker = User::factory()->worker()->create();
    $this->actingAs($worker);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('worker.dashboard'));
});