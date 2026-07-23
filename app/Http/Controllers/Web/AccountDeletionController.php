<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AccountDeletionRequest;
use App\Models\User;
use Illuminate\Http\Request;

class AccountDeletionController extends Controller
{
    /**
     * Show the public account-deletion request form.
     */
    public function create()
    {
        return view('pages.account-deletion.create');
    }

    /**
     * Store a new account-deletion request for HR to review.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        AccountDeletionRequest::create([
            'user_id' => User::where('email', $data['email'])->value('id'),
            'name' => $data['name'],
            'email' => $data['email'],
            'reason' => $data['reason'] ?? null,
            'status' => AccountDeletionRequest::STATUS_PENDING,
        ]);

        return back()->with('success', 'Permintaan penghapusan akun Anda telah diterima dan akan diproses oleh HR.');
    }
}
