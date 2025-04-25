<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Não autenticado'], 401);
        }

        // Verificar se o usuário é realmente um admin no banco de dados
        if ($user->type !== 'admin') {
            Log::error('Non-admin user trying to access admin route', [
                'user_id' => $user->id,
                'email' => $user->email,
                'type' => $user->type
            ]);
            
            return response()->json([
                'message' => 'Acesso não autorizado. Apenas administradores podem acessar este recurso.',
                'user_type' => $user->type
            ], 403);
        }

        $token = $request->user()->currentAccessToken();
        
        Log::debug('AdminMiddleware - Token abilities check', [
            'user_id' => $user->id,
            'email' => $user->email,
            'token_abilities' => $token ? $token->abilities : 'No token',
            'expected' => ['admin']
        ]);

        if (!$token || !in_array('admin', $token->abilities)) {
            return response()->json([
                'message' => 'Acesso não autorizado. Apenas administradores podem acessar este recurso.',
                'user_type' => $user->type,
                'token_abilities' => $token ? $token->abilities : 'No token'
            ], 403);
        }

        return $next($request);
    }
}
