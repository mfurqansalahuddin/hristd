<?php

use App\Events\LocationPinged;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use App\Models\UserCurrentLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

afterEach(fn () => Carbon::setTestNow());

test('ping upsert lokasi terkini di dalam jam kerja', function () {
    Event::fake([LocationPinged::class]);
    $user = User::factory()->create();
    Carbon::setTestNow(Carbon::create(2026, 7, 14, 10, 0));

    $this->actingAs($user, 'sanctum')->postJson('/api/location/ping', ['lat' => 5.5, 'long' => 95.3])
        ->assertOk()->assertJson(['ok' => true]);

    expect(UserCurrentLocation::where('user_id', $user->id)->exists())->toBeTrue();
    Event::assertDispatched(LocationPinged::class, fn (LocationPinged $event) => $event->user->is($user));
});

test('ping dengan mocked=true tersimpan dan tampil di colleagues', function () {
    Event::fake([LocationPinged::class]);
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $kasi1 = User::factory()->create(['job_level' => 3, 'department_id' => $bagian->id]);
    $kasi2 = User::factory()->create(['job_level' => 3, 'department_id' => $bagian->id]);
    Carbon::setTestNow(Carbon::create(2026, 7, 14, 10, 0));

    $this->actingAs($kasi2, 'sanctum')->postJson('/api/location/ping', ['lat' => 5.5, 'long' => 95.3, 'mocked' => true])->assertOk();

    expect(UserCurrentLocation::where('user_id', $kasi2->id)->first()->is_mock_location)->toBeTrue();

    $response = $this->actingAs($kasi1, 'sanctum')->getJson('/api/location/colleagues')->assertOk();
    expect($response->json('data.0.is_mock_location'))->toBeTrue();
});

test('ping no-op di luar jam kerja (>17:00)', function () {
    Event::fake([LocationPinged::class]);
    $user = User::factory()->create();
    Carbon::setTestNow(Carbon::create(2026, 7, 14, 18, 0));

    $this->actingAs($user, 'sanctum')->postJson('/api/location/ping', ['lat' => 5.5, 'long' => 95.3])->assertOk();

    expect(UserCurrentLocation::where('user_id', $user->id)->exists())->toBeFalse();
    Event::assertNotDispatched(LocationPinged::class);
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

test('list departemen untuk sub-filter Direksi', function () {
    $user = User::factory()->create(['job_level' => 1]);
    Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/location/departments')->assertOk();

    expect($response->json('data.0.name'))->toBe('Bagian Umum');
});

test('colleagues menyertakan photo_url', function () {
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $kasi1 = User::factory()->create(['job_level' => 3, 'department_id' => $bagian->id]);
    $kasi2 = User::factory()->create(['job_level' => 3, 'department_id' => $bagian->id]);

    UserCurrentLocation::create(['user_id' => $kasi2->id, 'lat' => 5.5, 'long' => 95.3, 'last_updated_at' => now()]);

    $response = $this->actingAs($kasi1, 'sanctum')->getJson('/api/location/colleagues')->assertOk();

    expect($response->json('data.0.photo_url'))->toBeString();
});

test('daftar penanda lokasi kantor untuk minimap', function () {
    $user = User::factory()->create();
    Location::create(['name' => 'Kantor Pusat', 'type' => 'RADIUS', 'lat' => 5.5, 'long' => 95.3, 'radius_meters' => 100]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/location/offices')->assertOk();

    expect($response->json('data.0.name'))->toBe('Kantor Pusat');
});

test('is_online true kalau update kurang dari 3 menit lalu', function () {
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $kasi1 = User::factory()->create(['job_level' => 3, 'department_id' => $bagian->id]);
    $kasi2 = User::factory()->create(['job_level' => 3, 'department_id' => $bagian->id]);

    UserCurrentLocation::create(['user_id' => $kasi2->id, 'lat' => 5.5, 'long' => 95.3, 'last_updated_at' => now()->subMinutes(1)]);

    $response = $this->actingAs($kasi1, 'sanctum')->getJson('/api/location/colleagues')->assertOk();

    expect($response->json('data.0.is_online'))->toBeTrue();
});
