<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $redirectPath = match ($user->type) {
                'admin' => '/admin/dashboard',
                'courier' => '/courier/dashboard',
                'partner' => '/partner/dashboard',
                default => '/customer/dashboard',
            };
            return redirect($redirectPath);
        }

        return Inertia::render('Home');
    }
}
