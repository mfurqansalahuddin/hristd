<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class DeletionRequestController extends Controller
{
    public function index()
    {
        return view('pages.admin.deletion-requests.index', [
            'title' => 'Permintaan Hapus Akun',
        ]);
    }
}
