<?php

use App\Models\Department;
use App\Models\User;
use App\Models\UserCurrentLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

afterEach(fn () => Carbon::setTestNow());

test('ping upsert lokasi terkini di dalam jam kerja', function () {
    $user = User::factory()->create();
    Carbon::setTestNow(Carbon::create(2026, 7, 14, 10, 0));

    $this->actingAs($user, 'sanctum')->postJson('/api/location/ping', ['lat' => 5.5, 'long' => 95.3])
        ->assertOk()->assertJson(['ok' => true]);

    expect(UserCurrentLocation::where('user_id', $user->id)->exists())->toBeTrue();
});

test('ping no-op di luar jam kerja (>17:00)', function () {
    $user = User::factory()->create();
    Carbon::setTestNow(Carbon::create(2026, 7, 14, 18, 0));

    $this->actingAs($user, 'sanctum')->postJson('/api/location/ping', ['lat' => 5.5, 'long' => 95.3])->assertOk();

    expect(UserCurrentLocation::where('user_id', $user->id)->exists())->toBeFalse();
});

test('staf hanya lihat rekan 1 seksi via endpoint colleagues', function () {
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);

    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    $rekan = User::factory()->create(['job_level' => 4, 'department_id' => $seksi->id]);
    $pejabat = User::factory()->create(['job_level' => 2, 'department_id' => $bagian->id]);

    foreach ([$rekan, $pejabat] as $u) {
        UserCurrentLocation::create(['user_id' => $u->id, 'lat' => 5.5, 'long' => 95.3, 'last_updated_at' => now()]);
    }

    $response = $this->actingAs($staf, 'sanctum')->getJson('/api/location/colleagues')->assertOk();

    expect($response->json('data.*.user_id'))->toContain($rekan->id)->not->toContain($pejabat->id);
});

test('staf tidak boleh minta scope subordinates', function () {
    $staf = User::factory()->create(['job_level' => 4]);

    $this->actingAs($staf, 'sanctum')->getJson('/api/location/colleagues?scope=subordinates')->assertStatus(403);
});

test('is_online true kalau update kurang dari 3 menit lalu', function () {
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $kasi1 = User::factory()->create(['job_level' => 3, 'department_id' => $bagian->id]);
    $kasi2 = User::factory()->create(['job_level' => 3, 'department_id' => $bagian->id]);

    UserCurrentLocation::create(['user_id' => $kasi2->id, 'lat' => 5.5, 'long' => 95.3, 'last_updated_at' => now()->subMinutes(1)]);

    $response = $this->actingAs($kasi1, 'sanctum')->getJson('/api/location/colleagues')->assertOk();

    expect($response->json('data.0.is_online'))->toBeTrue();
});
