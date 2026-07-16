<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** §15.6 mobile-app.md — mirror Web\ProfileController::update versi mobile. */
class ProfileController extends Controller
{
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
