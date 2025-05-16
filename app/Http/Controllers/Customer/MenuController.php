<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function show($partnerId)
    {
        return Inertia::render('Customer/Menu', [
            'partnerId' => $partnerId,
        ]);
    }
}
