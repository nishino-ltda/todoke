<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'telefone' => 'required|string|max:20',
                'tipo' => 'required|string|in:entregador,cliente,parceiro',
                'password' => 'required|string|min:8',
            ]);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            // Retorna 409 apenas se o único erro for de email duplicado
            if (count($errors) === 1 && isset($errors['email']) && $errors['email'][0] === 'The email has already been taken.') {
                return response()->json([
                    'message' => 'Email já cadastrado',
                    'errors' => $errors
                ], 409);
            }
            throw $e;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'tipo' => $request->tipo,
            'password' => Hash::make($request->password),
            'status' => 'ativo',
        ]);

        $token = $user->createToken('auth_token', [$user->tipo])->plainTextToken;

        Log::debug('Token generated for user', [
            'user_id' => $user->id,
            'email' => $user->email,
            'tipo' => $user->tipo,
            'abilities' => $user->tokens()->latest()->first()->abilities
        ]);

        return response()->json([
            'token' => $token,
            'usuario' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tipo' => $user->tipo,
            ]
        ], 201);
    }

    /**
     * Login user and create token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        // Delete any existing tokens for the user
        $user->tokens()->delete();
        
        // Verify user type before creating token
        $abilities = [$user->tipo];

        // Debug antes de criar o token
        Log::debug('Creating token for user', [
            'user_id' => $user->id,
            'email' => $user->email,
            'tipo' => $user->tipo,
            'expected_abilities' => $abilities
        ]);

        $token = $user->createToken('auth_token', $abilities)->plainTextToken;

        $createdToken = $user->tokens()->latest()->first();
        
        // Verificar se o token foi criado corretamente
        if ($createdToken->tokenable_id !== $user->id) {
            Log::error('Token created for wrong user', [
                'expected_user_id' => $user->id,
                'token_user_id' => $createdToken->tokenable_id
            ]);
            throw new \Exception('Token created for wrong user');
        }

        // Debug após criar o token
        Log::debug('Token created', [
            'user_id' => $user->id,
            'token_id' => $createdToken->id,
            'abilities' => $createdToken->abilities,
            'token_type' => get_class($createdToken)
        ]);
        
        Log::debug('Login token generated', [
            'user_id' => $user->id,
            'tipo' => $user->tipo,
            'requested_abilities' => $abilities,
            'token_abilities' => $createdToken->abilities,
            'token_id' => $createdToken->id
        ]);

        // Additional verification
        if ($user->tipo !== 'admin' && in_array('admin', $createdToken->abilities)) {
            Log::error('Invalid abilities assigned to non-admin user', [
                'user_id' => $user->id,
                'expected_abilities' => $abilities,
                'actual_abilities' => $createdToken->abilities
            ]);
            throw new \Exception('Invalid token abilities assigned');
        }

        return response()->json([
            'token' => $token,
            'usuario' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tipo' => $user->tipo,
            ]
        ]);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso']);
    }
}
