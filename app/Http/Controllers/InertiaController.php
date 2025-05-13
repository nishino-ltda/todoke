<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class InertiaController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('App');
    }

    public function customerDashboard()
    {
        return Inertia::render('Customer/Dashboard');
    }

    public function courierDashboard()
    {
        return Inertia::render('Courier/Dashboard');
    }

    public function partnerDashboard()
    {
        return Inertia::render('Partner/Dashboard');
    }
}
