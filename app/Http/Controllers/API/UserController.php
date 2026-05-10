<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Get the authenticated user's profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'type' => $user->type,
            'photoUrl' => $user->fotoUrl ?? null,
            'status' => $user->status
        ]);
    }

    /**
     * Update the authenticated user's profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,'.$request->user()->id,
            'phone' => 'sometimes|string|max:20',
            'photoUrl' => 'sometimes|nullable|string|max:255',
        ]);

        $user = $request->user();
        
        $originalEmail = $user->email;
        $user->update($request->only(['name', 'email', 'phone', 'photoUrl']));

        // Reset email verification if email changed
        if ($request->has('email') && $originalEmail !== $request->email) {
            $user->email_verified_at = null;
            $user->save();
        }

        return response()->json($user->only(['name', 'email', 'phone', 'photoUrl']));
    }

    /**
     * Delete the authenticated user's account
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required|string|current_password:sanctum',
        ]);

        $user = $request->user();
        
        // Log out all tokens
        $user->tokens()->delete();
        
        // Delete user
        $user->delete();

        return response()->noContent();
    }

    /**
     * List all users (admin only)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->user()->type !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $query = User::query();
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $users = $query->paginate(10);
        
        return response()->json([
            'data' => $users->items(),
            'total' => $users->total(),
            'per_page' => $users->perPage(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
        ]);
    }

    /**
     * Update user status (admin only)
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        if ($request->user()->type !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'status' => 'required|string|in:active,inactive',
        ]);

        $user = User::findOrFail($id);
        $user->status = $request->status;
        $user->save();

        return response()->json(['status' => $user->status]);
    }

    /**
     * Get platform statistics (admin only)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        if (Auth::user()->type !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $activeUsers = User::where('status', 'active')->count();
        
        // Aqui você pode adicionar mais estatísticas conforme necessário
        // Por exemplo, contagem de entregas por status, etc.
        
        return response()->json([
            'total_users' => User::count(),
            'active_users' => $activeUsers,
            'active_deliveries' => 0, // Implementar contagem real
            'total_nodes' => \App\Models\Node::count(),
            'reported_issues' => 0,
            'deliveries_today' => 0,
            'deliveries_status' => [
                'pending' => 0,
                'in_transit' => 0,
                'delivered' => 0,
            ]
        ]);
    }

    /**
     * Unlock a user account (admin only)
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlock($id)
    {
        if (Auth::user()->type !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $user = User::findOrFail($id);
        
        if (!$user->isLocked()) {
            return response()->json([
                'message' => 'Account is not locked'
            ], 400);
        }

        $user->unlockAccount();
        
        Log::info('Account unlocked by admin', [
            'admin_id' => Auth::id(),
            'user_id' => $user->id
        ]);

        return response()->json([
            'message' => 'Account unlocked successfully',
            'locked_at' => null
        ]);
    }

    /**
     * Get platform settings (admin only)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSettings()
    {
        if (Auth::user()->type !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Mock settings for now as there is no settings table yet
        return response()->json([
            'site_name' => 'TODOKE',
            'maintenance_mode' => false,
            'allow_registration' => true,
            'delivery_fee_base' => 5.00,
            'delivery_fee_per_km' => 1.50,
            'support_email' => 'support@todoke.com',
            'contact_phone' => '+55 11 99999-9999'
        ]);
    }

    /**
     * Update platform settings (admin only)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSettings(Request $request)
    {
        if (Auth::user()->type !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // In a real app, you would save these to a settings table or file
        // For now, we just return success
        return response()->json([
            'message' => 'Settings updated successfully',
            'data' => $request->all()
        ]);
    }
}
