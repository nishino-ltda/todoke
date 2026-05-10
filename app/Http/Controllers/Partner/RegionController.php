<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Region;
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
        $region = Region::where('partner_id', auth()->id())->findOrFail($id);
        
        return Inertia::render('Partner/Regions/Edit', [
            'region' => $region,
        ]);
    }

    public function show($id)
    {
        $region = Region::where('partner_id', auth()->id())->findOrFail($id);

        return Inertia::render('Partner/Regions/Show', [
            'region' => $region,
        ]);
    }
}
