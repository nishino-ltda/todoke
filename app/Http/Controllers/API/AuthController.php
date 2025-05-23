<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Verified;

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
                'phone' => 'required|string|max:20',
                'type' => 'required|string|in:courier,customer,partner',
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
            'phone' => $request->phone,
            'type' => $request->type,
            'password' => Hash::make($request->password),
            'status' => 'active',
        ]);

        $token = $user->createToken('auth_token', [$user->type])->plainTextToken;

        Log::debug('Token generated for user', [
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => $user->type,
            'abilities' => $user->tokens()->latest()->first()->abilities
        ]);

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'type' => $user->type,
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
        Log::debug('Login request received', ['email' => $request->email]);
        
        $request->validate([
            'email' => 'required|email|regex:/^.+@.+\..+$/',
            'password' => 'required|string|min:8',
        ], [
            'email.regex' => 'O email deve ser um endereço válido',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres'
        ]);

        Log::debug('Request validated successfully');
        
        $user = User::where('email', $request->email)->first();
        Log::debug('User lookup result', ['user_exists' => !!$user]);

        if (!$user) {
            Log::debug('User not found for email', ['email' => $request->email]);
            return response()->json([
                'message' => 'As credenciais fornecidas estão incorretas',
                'errors' => [
                    'email' => ['As credenciais fornecidas estão incorretas.']
                ]
            ], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            Log::debug('Password mismatch for user', ['user_id' => $user->id]);
            return response()->json([
                'message' => 'As credenciais fornecidas estão incorretas',
                'errors' => [
                    'email' => ['As credenciais fornecidas estão incorretas.']
                ]
            ], 401);
        }

        Log::debug('Credentials validated successfully', ['user_id' => $user->id]);
        
        // Delete any existing tokens for the user
        $user->tokens()->delete();
        Log::debug('Deleted existing tokens for user', ['user_id' => $user->id]);
        
        // Verify user type before creating token
        $abilities = [$user->type];

        // Debug antes de criar o token
        Log::debug('Creating token for user', [
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => $user->type,
            'expected_abilities' => $abilities
        ]);

        $token = $user->createToken('auth_token', $abilities)->plainTextToken;
        Log::debug('Created new token for user', [
            'user_id' => $user->id,
            'token_abilities' => $abilities
        ]);

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
            'type' => $user->type,
            'requested_abilities' => $abilities,
            'token_abilities' => $createdToken->abilities,
            'token_id' => $createdToken->id
        ]);

        // Additional verification
        if ($user->type !== 'admin' && in_array('admin', $createdToken->abilities)) {
            Log::error('Invalid abilities assigned to non-admin user', [
                'user_id' => $user->id,
                'expected_abilities' => $abilities,
                'actual_abilities' => $createdToken->abilities
            ]);
            throw new \Exception('Invalid token abilities assigned');
        }

        Log::debug('Returning successful login response');
        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'type' => $user->type,
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

    /**
     * Send password reset link
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['error' => __($status)], 400);
    }

    /**
     * Send email verification notification
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified']);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent']);
    }

    /**
     * Verify email
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified']);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return response()->json(['message' => 'Email verified successfully']);
    }

    /**
     * Confirm password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $request->user()->password)) {
            return response()->json([
                'message' => 'Password confirmation failed',
                'errors' => ['password' => ['The provided password does not match our records.']]
            ], 422);
        }

        return response()->json([
            'message' => 'Password confirmed successfully',
            'confirmed_at' => time()
        ]);
    }

    /**
     * Update password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            return response()->json([
                'message' => 'Password update failed',
                'errors' => ['current_password' => ['The provided password does not match our records.']]
            ], 422);
        }

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Password updated successfully']);
    }

    /**
     * Reset password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['error' => __($status)], 400);
    }
}
