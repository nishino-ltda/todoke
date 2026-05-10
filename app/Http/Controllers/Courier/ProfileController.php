<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function index()
    {
        return Inertia::render('Courier/Profile');
    }
}
