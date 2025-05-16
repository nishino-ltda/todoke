<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SystemMonitorController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/System/Monitor');
    }

    public function logs()
    {
        return Inertia::render('Admin/System/Logs');
    }
}
