<?php

use App\Models\KpiPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('current mengembalikan periode paling baru', function () {
    KpiPeriod::create(['month' => 6, 'year' => 2026, 'status' => 'CLOSED']);
    $latest = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')->getJson('/api/kpi/periods/current')
        ->assertOk()
        ->assertJson(['data' => ['id' => $latest->id, 'month' => 7, 'year' => 2026, 'status' => 'DRAFT']]);
});

test('data null kalau belum ada periode sama sekali', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')->getJson('/api/kpi/periods/current')
        ->assertOk()
        ->assertJson(['data' => null]);
});
