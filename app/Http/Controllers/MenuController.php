<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Node;

class MenuController extends Controller
{
    public function show($nodeSlug)
    {
        $node = Node::where('slug', $nodeSlug)
            ->with('products')
            ->firstOrFail();

        return Inertia::render('Customer/Menu', [
            'restaurant' => $node
        ]);
    }
}
