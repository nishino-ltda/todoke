<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the partner dashboard.
     */
    public function index()
    {
        return Inertia::render('Partner/Dashboard/Index');
    }
}
