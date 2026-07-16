<?php

use App\Models\DailyActivity;
use App\Models\KpiPeriod;
use App\Models\KpiPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('bisa input logbook untuk hari ini dan H-2', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')->postJson('/api/logbook', [
        'activity_date' => now()->toDateString(), 'description' => 'Kerja hari ini',
    ])->assertCreated();

    $this->actingAs($user, 'sanctum')->postJson('/api/logbook', [
        'activity_date' => now()->subDays(2)->toDateString(), 'description' => 'Kerja 2 hari lalu',
    ])->assertCreated();

    expect(DailyActivity::where('user_id', $user->id)->count())->toBe(2);
});

test('logbook lebih dari H-2 mundur ditolak', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')->postJson('/api/logbook', [
        'activity_date' => now()->subDays(3)->toDateString(), 'description' => 'Terlalu lama',
    ])->assertStatus(422)->assertJsonValidationErrors('activity_date');
});

test('tidak bisa kaitkan logbook ke rencana kerja orang lain', function () {
    $period = KpiPeriod::create(['month' => 7, 'year' => 2026, 'status' => 'DRAFT']);
    $user = User::factory()->create();
    $orangLain = User::factory()->create();
    $plan = KpiPlan::create(['user_id' => $orangLain->id, 'period_id' => $period->id, 'target_description' => 'A', 'weight' => 20, 'status' => 'DRAFT']);

    $this->actingAs($user, 'sanctum')->postJson('/api/logbook', [
        'activity_date' => now()->toDateString(), 'description' => 'Coba kaitkan', 'kpi_plan_id' => $plan->id,
    ])->assertStatus(403);
});
