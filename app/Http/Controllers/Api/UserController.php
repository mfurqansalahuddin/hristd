<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Profil user yang sedang login, diperkaya dengan department, jabatan_label,
     * dan photo_url (§15.1 mobile-app.md) supaya Home & Profil mobile tidak
     * perlu 2x call.
     */
    public function show(Request $request)
    {
        $user = $request->user()->load('department');

        return response()->json([
            ...$user->toArray(),
            'jabatan_label' => $user->jabatanLabel(),
            'photo_url' => $user->photoUrl(),
        ]);
    }
}
