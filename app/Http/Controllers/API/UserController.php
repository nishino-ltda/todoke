<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'nome' => $user->nome,
            'email' => $user->email,
            'telefone' => $user->telefone,
            'tipo' => $user->tipo,
            'fotoUrl' => $user->fotoUrl ?? null,
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
            'nome' => 'sometimes|string|max:255',
            'telefone' => 'sometimes|string|max:20',
            'fotoUrl' => 'sometimes|nullable|string|max:255',
        ]);

        $user = $request->user();
        
        $user->update($request->only(['nome', 'telefone', 'fotoUrl']));

        return response()->json($user->only(['nome', 'telefone', 'fotoUrl']));
    }

    /**
     * List all users (admin only)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = User::query();
        
        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
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
        $request->validate([
            'status' => 'required|string|in:ativo,inativo',
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
        $activeUsers = User::where('status', 'ativo')->count();
        
        // Aqui você pode adicionar mais estatísticas conforme necessário
        // Por exemplo, contagem de entregas por status, etc.
        
        return response()->json([
            'usuariosAtivos' => $activeUsers,
            'entregasHoje' => 0, // Implementar contagem real
            'entregasStatus' => [
                'pendente' => 0, // Implementar contagem real
                'em_andamento' => 0, // Implementar contagem real
                'concluida' => 0, // Implementar contagem real
            ]
        ]);
    }
}
