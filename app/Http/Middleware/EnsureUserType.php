<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$types
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$types)
    {
        $user = $request->user();
        foreach ($types as $type) {
            if ($user->type === $type || $user->hasRole($type)) {
                return $next($request);
            }
        }

        return redirect()->route('login');
    }
}
