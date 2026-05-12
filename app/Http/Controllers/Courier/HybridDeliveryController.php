<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HybridDeliveryController extends Controller
{
    public function index()
    {
        return Inertia::render('Courier/HybridDeliveries/Index');
    }

    public function show($id)
    {
        return Inertia::render('Courier/HybridDeliveries/Show', [
            'hybridDeliveryId' => $id,
        ]);
    }
    public function create()
    {
        return Inertia::render('Courier/HybridDeliveries/Create');
    }

    public function handoff($id)
    {
        return Inertia::render('Courier/HybridDeliveries/Handoff', [
            'deliveryId' => $id,
        ]);
    }
}
