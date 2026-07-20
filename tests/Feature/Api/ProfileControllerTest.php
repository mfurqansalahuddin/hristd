<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('edit akun per field: ubah username saja tanpa password saat ini', function () {
    $user = User::factory()->create(['username' => 'lama', 'email' => 'tetap@example.com']);

    $this->actingAs($user, 'sanctum')->putJson('/api/profile', ['username' => 'baru'])
        ->assertOk()->assertJsonPath('username', 'baru');

    expect($user->fresh()->email)->toBe('tetap@example.com');
});

test('ganti password butuh password saat ini yang benar', function () {
    $user = User::factory()->create(['password' => 'rahasia123']);

    $this->actingAs($user, 'sanctum')->putJson('/api/profile', [
        'password' => 'passwordbaru',
        'password_confirmation' => 'passwordbaru',
        'current_password' => 'salah',
    ])->assertStatus(422)->assertJsonValidationErrors('current_password');

    $this->actingAs($user, 'sanctum')->putJson('/api/profile', [
        'password' => 'passwordbaru',
        'password_confirmation' => 'passwordbaru',
        'current_password' => 'rahasia123',
    ])->assertOk();

    expect(Hash::check('passwordbaru', $user->fresh()->password))->toBeTrue();
});

test('edit akun ditolak kalau username atau email sudah dipakai user lain', function () {
    User::factory()->create(['username' => 'terpakai']);
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')->putJson('/api/profile', ['username' => 'terpakai'])
        ->assertStatus(422)->assertJsonValidationErrors('username');
});
