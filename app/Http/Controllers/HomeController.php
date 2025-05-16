<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        return Inertia::render('Home');
    }

    public function dashboard()
    {
        // This will be updated later to redirect based on user role
        return Inertia::render('Dashboard');
    }
}
