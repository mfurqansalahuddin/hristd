<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/** §15.5 mobile-app.md — autocomplete form aduan, versi non-admin. */
class UserSearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->string('q')->trim()->toString();

        $users = User::query()
            ->when($query !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$query}%")->orWhere('nik', 'like', "%{$query}%")))
            ->with('department')
            ->limit(20)
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'nik' => $user->nik,
                'department' => $user->department?->name,
            ]);

        return response()->json(['data' => $users]);
    }
}
