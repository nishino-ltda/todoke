<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Update the user's locale preference
     */
    public function updateLocale(Request $request)
    {
        $validated = $request->validate([
            'locale' => 'required|in:en,pt-BR'
        ]);

        $user = Auth::user();
        $user->locale = $validated['locale'];
        $user->save();

        return response()->json([
            'message' => 'Locale updated successfully',
            'locale' => $user->locale
        ]);
    }
}
