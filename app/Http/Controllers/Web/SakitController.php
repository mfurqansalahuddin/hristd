<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class SakitController extends Controller
{
    public function index()
    {
        return view('pages.admin.sakit.index', [
            'title' => 'Sakit',
        ]);
    }
}
