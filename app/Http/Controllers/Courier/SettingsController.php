<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Display the courier settings page.
     */
    public function index()
    {
        return Inertia::render('Courier/Settings/Index');
    }

    /**
     * Update the courier settings.
     */
    public function update(Request $request)
    {
        // Placeholder for update logic
        return response()->json(['message' => 'Settings updated (placeholder)']);
    }
}
