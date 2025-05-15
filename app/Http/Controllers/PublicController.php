<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

/**
 * Use only for inertia renders, all functionality must be done by API
 */

class PublicController extends Controller
{
    

    public function index()
    {
        return Inertia::render('Welcome');
    }

    public function login()
    {
        return Inertia::render('auth/Login');
    }

    public function register()
    {
        return Inertia::render('auth/Register');
    }
}
