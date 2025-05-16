<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AddonController extends Controller
{
    /**
     * Display a listing of the partner's addons.
     */
    public function index()
    {
        return Inertia::render('Partner/Addons/Index');
    }

    /**
     * Show the form for creating a new addon.
     */
    public function create()
    {
        return Inertia::render('Partner/Addons/Create');
    }

    /**
     * Store a newly created addon in storage.
     */
    public function store(Request $request)
    {
        // Placeholder for store logic
        return response()->json(['message' => 'Addon stored (placeholder)']);
    }

    /**
     * Display the specified addon.
     */
    public function show($addonId)
    {
        return Inertia::render('Partner/Addons/Show', [
            'addonId' => $addonId,
        ]);
    }

    /**
     * Show the form for editing the specified addon.
     */
    public function edit($addonId)
    {
        return Inertia::render('Partner/Addons/Edit', [
            'addonId' => $addonId,
        ]);
    }

    /**
     * Update the specified addon in storage.
     */
    public function update(Request $request, $addonId)
    {
        // Placeholder for update logic
        return response()->json(['message' => 'Addon updated (placeholder)']);
    }

    /**
     * Remove the specified addon from storage.
     */
    public function destroy($addonId)
    {
        // Placeholder for destroy logic
        return response()->json(['message' => 'Addon deleted (placeholder)']);
    }
}
