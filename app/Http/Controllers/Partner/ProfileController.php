<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function index()
    {
        return Inertia::render('Partner/Profile');
    }
}
