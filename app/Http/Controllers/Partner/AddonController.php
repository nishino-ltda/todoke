<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AddonController extends Controller
{
    public function index()
    {
        return Inertia::render('Partner/Addons/Index');
    }

    public function create()
    {
        return Inertia::render('Partner/Addons/Create');
    }

    public function edit($id)
    {
        return Inertia::render('Partner/Addons/Edit', [
            'addonId' => $id,
        ]);
    }

    public function show($id)
    {
        return Inertia::render('Partner/Addons/Show', [
            'addonId' => $id,
        ]);
    }
}
