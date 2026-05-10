<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $baseRules = [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'required|string|max:20',
                'cpf' => 'required|string|max:14|unique:users',
                'type' => 'required|string|in:courier,customer,partner',
                'password' => 'required|string|min:8|confirmed',
            ];

            $courierRules = [
                'license_number' => 'required_if:type,courier|string|max:50',
                'vehicle_type' => 'required_if:type,courier|string|in:motorcycle,car,bicycle',
                'license_file' => 'required_if:type,courier|file|mimes:jpg,png,pdf|max:2048',
            ];

            $partnerRules = [
                'business_name' => 'required_if:type,partner|string|max:255',
                'business_type' => 'required_if:type,partner|string|in:restaurant,market,pharmacy',
                'tax_id' => 'required_if:type,partner|string|max:20',
                'address' => 'required_if:type,partner|string|max:255',
                'business_document' => 'required_if:type,partner|file|mimes:jpg,png,pdf|max:2048',
            ];

            $rules = array_merge($baseRules, 
                $request->type === 'courier' ? $courierRules : [],
                $request->type === 'partner' ? $partnerRules : []
            );

            $request->validate($rules);
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

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'cpf' => $request->cpf,
            'type' => $request->type,
            'password' => Hash::make($request->password),
            'status' => 'active',
        ];

        // Handle courier specific data
        if ($request->type === 'courier') {
            $licensePath = $request->file('license_file')->store('licenses');
            $userData['license_number'] = $request->license_number;
            $userData['vehicle_type'] = $request->vehicle_type;
            $userData['license_file_path'] = $licensePath;
        }

        // Handle partner specific data
        if ($request->type === 'partner') {
            $documentPath = $request->file('business_document')->store('business_documents');
            $userData['business_name'] = $request->business_name;
            $userData['business_type'] = $request->business_type;
            $userData['tax_id'] = $request->tax_id;
            $userData['address'] = $request->address;
            $userData['business_document_path'] = $documentPath;
        }

        $user = User::create($userData);

        // Se registrou como courier ou partner, adiciona role customer
        if (in_array($request->type, ['courier', 'partner'])) {
            $user->addRole('customer');
        }

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
                'all_roles' => $user->allRoles(),
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
            $message = $request->header('Accept-Language') === 'en' 
                ? 'The provided credentials are incorrect'
                : 'As credenciais fornecidas estão incorretas';
            return response()->json([
                'message' => $message,
                'errors' => [
                    'email' => [$message]
                ]
            ], 401);
        }

        // Check if account is locked
        if ($user->isLocked()) {
            Log::debug('Login attempt for locked account', ['user_id' => $user->id]);
            $message = $request->header('Accept-Language') === 'en'
                ? 'Account locked. Please contact support.'
                : 'Conta bloqueada. Por favor, entre em contato com o suporte.';
            return response()->json([
                'message' => $message,
                'errors' => [
                    'email' => [$message]
                ]
            ], 423); // 423 Locked status code
        }

        if (!Hash::check($request->password, $user->password)) {
            Log::debug('Password mismatch for user', ['user_id' => $user->id]);
            
            // Record failed attempt
            $user->recordFailedAttempt();
            
            // Lock account after 5 failed attempts
            if ($user->failed_attempts >= 5) {
                $user->lockAccount();
                Log::warning('Account locked due to too many failed attempts', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
                
                $message = $request->header('Accept-Language') === 'en'
                    ? 'Account locked due to too many failed attempts. Please contact support.'
                    : 'Conta bloqueada devido a muitas tentativas falhas. Por favor, entre em contato com o suporte.';
                return response()->json([
                    'message' => $message,
                    'errors' => [
                        'email' => [$message]
                    ]
                ], 423);
            }

            $message = $request->header('Accept-Language') === 'en' 
                ? 'The provided credentials are incorrect'
                : 'As credenciais fornecidas estão incorretas';
            return response()->json([
                'message' => $message,
                'errors' => [
                    'email' => [$message]
                ]
            ], 401);
        }

        // Reset failed attempts on successful login
        $user->resetFailedAttempts();

        Log::debug('Credentials validated successfully', ['user_id' => $user->id]);
        
        // Log the user in via session
        Auth::login($user);
        
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
                'all_roles' => $user->allRoles(),
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
        $user = $request->user();
        
        // Handle persistent tokens (those stored in database)
        if ($token = $user->currentAccessToken()) {
            if (method_exists($token, 'delete')) {
                $token->delete();
            }
        }

        // Delete all other tokens (for persistent tokens)
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        // Clear web session if exists
        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

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
     * Show password reset form
     *
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function showResetForm(Request $request, string $token)
    {
        return response()->json([
            'token' => $token,
            'email' => $request->email
        ]);
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
