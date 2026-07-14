<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('pages.admin.attendances.index', [
            'title' => 'Kehadiran',
        ]);
    }
}
