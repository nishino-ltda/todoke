<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $recentUsers = User::latest()->take(5)->get(['id', 'name', 'email', 'type', 'created_at']);

        return Inertia::render('Admin/Dashboard', [
            'recentUsers' => $recentUsers->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $user->type,
                    'created_at' => $user->created_at->toISOString(),
                ];
            }),
        ]);
    }
}
