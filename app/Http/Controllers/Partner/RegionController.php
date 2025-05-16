<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegionController extends Controller
{
    public function index()
    {
        return Inertia::render('Partner/Regions/Index');
    }

    public function create()
    {
        return Inertia::render('Partner/Regions/Create');
    }

    public function edit($id)
    {
        return Inertia::render('Partner/Regions/Edit', [
            'regionId' => $id,
        ]);
    }

    public function show($id)
    {
        return Inertia::render('Partner/Regions/Show', [
            'regionId' => $id,
        ]);
    }
}
