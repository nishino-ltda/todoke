<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with('restaurante');

        if ($request->has('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        $products = $query->where('status', 'disponivel')->get();

        return response()->json([
            'produtos' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'descricao' => $product->descricao,
                    'preco' => $product->preco,
                    'categoria' => $product->categoria,
                    'status' => $product->status,
                    'restaurante' => $product->restaurante->name
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'preco' => 'required|numeric|min:0',
            'categoria' => 'required|string|max:255'
        ]);

        $product = Product::create([
            'name' => $request->name,
            'descricao' => $request->descricao,
            'preco' => $request->preco,
            'categoria' => $request->categoria,
            'restauranteId' => $request->user()->id,
            'status' => 'disponivel'
        ]);

        return response()->json($product, 201);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'descricao' => 'nullable|string',
            'preco' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:disponivel,indisponivel'
        ]);

        $product->update($request->all());

        return response()->json($product, 200);
    }
}
