<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class PartnerController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Partner/Dashboard');
    }

    public function settings()
    {
        return Inertia::render('Partner/Settings');
    }
}
