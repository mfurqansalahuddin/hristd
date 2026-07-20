<?php

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function authChannel($actingUser, string $channel)
{
    return test()->actingAs($actingUser, 'sanctum')->postJson('/api/broadcasting/auth', [
        'channel_name' => $channel,
        'socket_id' => '1.1',
    ]);
}

test('staf hanya boleh subscribe channel departemennya sendiri', function () {
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $lain = Department::create(['name' => 'Bagian Lain', 'type' => 'BAGIAN']);
    $staf = User::factory()->create(['job_level' => 4, 'department_id' => $bagian->id]);

    authChannel($staf, 'private-department-locations.'.$bagian->id)->assertOk();
    authChannel($staf, 'private-department-locations.'.$lain->id)->assertForbidden();
    authChannel($staf, 'private-pejabat-locations')->assertForbidden();
});

test('kasi boleh subscribe pejabat-locations dan department-locations seksinya', function () {
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $seksi = Department::create(['name' => 'Seksi A', 'type' => 'SEKSI', 'parent_department_id' => $bagian->id]);
    $seksiLain = Department::create(['name' => 'Seksi Lain', 'type' => 'SEKSI']);
    $kasi = User::factory()->create(['job_level' => 3, 'department_id' => $seksi->id]);

    authChannel($kasi, 'private-pejabat-locations')->assertOk();
    authChannel($kasi, 'private-department-locations.'.$seksi->id)->assertOk();
    authChannel($kasi, 'private-department-locations.'.$seksiLain->id)->assertForbidden();
});

test('direksi boleh subscribe pejabat-locations dan department-locations manapun', function () {
    $bagian = Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $direksi = User::factory()->create(['job_level' => 1]);

    authChannel($direksi, 'private-pejabat-locations')->assertOk();
    authChannel($direksi, 'private-department-locations.'.$bagian->id)->assertOk();
});
