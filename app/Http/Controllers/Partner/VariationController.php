<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VariationController extends Controller
{
    public function index($product)
    {
        return Inertia::render('Partner/Products/Variations/Index', [
            'product' => $product
        ]);
    }

    public function create($product)
    {
        return Inertia::render('Partner/Products/Variations/Create', [
            'product' => $product
        ]);
    }

    public function edit($product, $variation)
    {
        return Inertia::render('Partner/Products/Variations/Edit', [
            'product' => $product,
            'variation' => $variation
        ]);
    }
}
