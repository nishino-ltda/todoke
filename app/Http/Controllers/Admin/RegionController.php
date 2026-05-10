<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegionController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Regions/Index');
    }

    public function create()
    {
        $partners = User::where('type', 'partner')->get(['id', 'name']);
        return Inertia::render('Admin/Regions/Create', [
            'partners' => $partners
        ]);
    }

    public function edit($id)
    {
        $region = Region::with('partner')->findOrFail($id);
        $partners = User::where('type', 'partner')->get(['id', 'name']);
        
        return Inertia::render('Admin/Regions/Edit', [
            'region' => $region,
            'partners' => $partners
        ]);
    }
}
