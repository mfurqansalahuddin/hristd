<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** §15.6 mobile-app.md — mirror Web\ProfileController::update versi mobile. */
class ProfileController extends Controller
{
    /**
     * Edit akun per field (username/email/password) — kirim hanya field yang diubah.
     * Ganti password wajib konfirmasi password saat ini; tidak ada alur reset via
     * email, jadi email cuma data kontak dan boleh diubah tanpa password.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'username' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'required', 'string', 'min:8', 'confirmed'],
            'current_password' => ['required_with:password', 'current_password:sanctum'],
        ]);

        unset($data['current_password']);
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        // Bentuk sama dengan GET /user supaya client bisa langsung simpan ulang.
        $user->load('department');

        return response()->json([
            ...$user->toArray(),
            'jabatan_label' => $user->jabatanLabel(),
            'photo_url' => $user->photoUrl(),
        ]);
    }

    public function updatePhoto(Request $request)
    {
        $request->validate(['photo' => ['required', 'image', 'max:5120']]);

        $user = $request->user();

        if ($user->photo_path) {
            Storage::disk('public')->delete($user->photo_path);
        }

        $user->update(['photo_path' => $request->file('photo')->store('employees', 'public')]);

        return response()->json(['photo_url' => $user->photoUrl()]);
    }
}
