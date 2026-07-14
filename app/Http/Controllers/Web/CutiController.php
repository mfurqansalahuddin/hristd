<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class CutiController extends Controller
{
    public function index()
    {
        return view('pages.admin.cuti.index', [
            'title' => 'Cuti',
        ]);
    }
}
