<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Display the partner settings page.
     */
    public function index()
    {
        return Inertia::render('Partner/Settings/Index');
    }

    /**
     * Update the partner settings.
     */
    public function update(Request $request)
    {
        // Placeholder for update logic
        return response()->json(['message' => 'Settings updated (placeholder)']);
    }
}
