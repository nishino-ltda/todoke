<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index()
    {
        return Inertia::render('Partner/Orders/Index');
    }

    public function show($id)
    {
        return Inertia::render('Partner/Orders/Show', [
            'orderId' => $id,
        ]);
    }
}
