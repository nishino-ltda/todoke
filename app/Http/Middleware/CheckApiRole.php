<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApiRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        foreach ($roles as $role) {
            if ($user->type === $role || $user->hasRole($role)) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
