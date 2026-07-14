<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class JabatanController extends Controller
{
    public function index()
    {
        return view('pages.admin.jabatan.index', [
            'title' => 'Manajemen Jabatan',
        ]);
    }
}
