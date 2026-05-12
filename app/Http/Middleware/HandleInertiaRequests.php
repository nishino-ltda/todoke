<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $settings = Setting::all()->pluck('value', 'key');

        return array_merge(parent::share($request), [
            'app' => [
                'name' => $settings->get('site_name', config('app.name')),
                'settings' => $settings->toArray(),
            ],
            'auth' => [
                'user' => \Illuminate\Support\Facades\Auth::user() ? [
                    'id' => \Illuminate\Support\Facades\Auth::user()->id,
                    'name' => \Illuminate\Support\Facades\Auth::user()->name,
                    'email' => \Illuminate\Support\Facades\Auth::user()->email,
                    'type' => \Illuminate\Support\Facades\Auth::user()->type,
                    'photoUrl' => \Illuminate\Support\Facades\Auth::user()->photoUrl,
                    'all_roles' => \Illuminate\Support\Facades\Auth::user()->all_roles,
                ] : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }
}
