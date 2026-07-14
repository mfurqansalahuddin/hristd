<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class DinasLuarController extends Controller
{
    public function index()
    {
        return view('pages.admin.dinas-luar.index', [
            'title' => 'Dinas Luar',
        ]);
    }
}
