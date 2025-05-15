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

    
}
