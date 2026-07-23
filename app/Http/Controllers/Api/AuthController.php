<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle an incoming authentication request. Login menerima email, NIK,
     * atau username (sama pola dengan Web\AuthController, §6/§10 plan.md).
     */
    public function login(LoginRequest $request)
    {
        $login = $request->string('login')->trim()->toString();
        $field = match (true) {
            filter_var($login, FILTER_VALIDATE_EMAIL) !== false => 'email',
            ctype_digit($login) => 'nik',
            default => 'username',
        };

        $user = User::where($field, $login)->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['The provided credentials do not match our records.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        // Bentuk sama dengan GET /user (§15.1 mobile-app.md) — client mengasumsikan
        // photo_url/jabatan_label selalu ada di setiap payload User.
        $user->load('department');

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                ...$user->toArray(),
                'jabatan_label' => $user->jabatanLabel(),
                'photo_url' => $user->photoUrl(),
            ],
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request)
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken $token */
        $token = $request->user()->currentAccessToken();
        $token->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}
