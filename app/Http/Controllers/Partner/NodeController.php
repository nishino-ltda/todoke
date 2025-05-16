<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NodeController extends Controller
{
    public function index()
    {
        return Inertia::render('Partner/Nodes/Index');
    }

    public function create()
    {
        return Inertia::render('Partner/Nodes/Create');
    }

    public function edit($id)
    {
        return Inertia::render('Partner/Nodes/Edit', [
            'nodeId' => $id,
        ]);
    }

    public function show($id)
    {
        return Inertia::render('Partner/Nodes/Show', [
            'nodeId' => $id,
        ]);
    }
}
