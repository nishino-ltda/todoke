<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        return Inertia::render('Partner/Products/Index');
    }

    public function create()
    {
        return Inertia::render('Partner/Products/Create');
    }

    public function edit($id)
    {
        return Inertia::render('Partner/Products/Edit', [
            'productId' => $id,
        ]);
    }

    public function show($id)
    {
        return Inertia::render('Partner/Products/Show', [
            'productId' => $id,
        ]);
    }
}
