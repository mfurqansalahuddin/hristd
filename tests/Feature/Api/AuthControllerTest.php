<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('login menerima email, nik, atau username', function (string $field) {
    $user = User::factory()->create(['nik' => '1234567890', 'username' => 'budi.s', 'email' => 'budi@tirtadaroy.id']);

    $response = $this->postJson('/api/login', [
        'login' => $user->{$field},
        'password' => 'password',
    ]);

    $response->assertOk()->assertJsonStructure(['token', 'user']);
})->with(['email', 'nik', 'username']);

test('login gagal dengan password salah', function () {
    $user = User::factory()->create(['email' => 'budi@tirtadaroy.id']);

    $this->postJson('/api/login', ['login' => $user->email, 'password' => 'salah'])
        ->assertStatus(422);
});

test('user terautentikasi bisa akses /api/user dan dapat data diperkaya', function () {
    $department = \App\Models\Department::create(['name' => 'Bagian Umum', 'type' => 'BAGIAN']);
    $user = User::factory()->create(['job_level' => 2, 'department_id' => $department->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('id', $user->id)
        ->assertJsonStructure(['jabatan_label', 'photo_url', 'department']);
});
