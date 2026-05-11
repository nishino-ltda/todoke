<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function index()
    {
        return Inertia::render('Customer/Profile', [
            'user' => request()->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        Log::info('Profile update initiated for user: ' . $user->email, [
            'name' => $request->name,
            'phone' => $request->phone,
            'has_photo' => $request->hasFile('photo')
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|max:2048',
        ]);
        
        $data = [
            'name' => $request->name,
            'phone' => $request->phone,
        ];

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('profiles', 'public');
            $data['photoUrl'] = $path;
            Log::info('Profile photo uploaded: ' . $path);
        }

        $user->update($data);
        
        Log::info('Profile updated successfully for user: ' . $user->email);

        return back()->with('success', 'Profile updated successfully');
    }
}
