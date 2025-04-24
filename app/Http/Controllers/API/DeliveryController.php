<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        // Debug para verificar o token recebido
        $bearerToken = $request->bearerToken();
        echo "\nToken recebido em index: " . $bearerToken;
        
        // Verificar o token manualmente
        $tokenParts = explode('|', $bearerToken);
        $tokenId = $tokenParts[0];
        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);
        
        if ($token) {
            $tokenUser = $token->tokenable;
            echo "\nToken ID: " . $token->id;
            echo "\nToken User ID: " . $token->tokenable_id;
            echo "\nToken User Tipo: " . $tokenUser->tipo;
            echo "\nToken Abilities: " . json_encode($token->abilities);
            
            // Usar o usuário do token diretamente
            $user = $tokenUser;
        } else {
            $user = $request->user();
        }
        
        echo "\nTipo do usuário listando entregas: " . $user->tipo;
        echo "\nID do usuário: " . $user->id;
        
        $query = Delivery::query();

        if ($user->tipo === 'cliente') {
            $query->where('clienteId', $user->id);
        } elseif ($user->tipo === 'entregador') {
            $query->where('entregadorId', $user->id);
        }

        $deliveries = $query->orderBy('createdAt', 'desc')
            ->paginate(15);

        return response()->json([
            'entregas' => $deliveries->items(),
            'total' => $deliveries->total(),
            'per_page' => $deliveries->perPage(),
            'current_page' => $deliveries->currentPage()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origem.lat' => 'required|numeric|between:-90,90',
            'origem.lng' => 'required|numeric|between:-180,180',
            'origem.endereco' => 'required|string|max:255',
            'destino.lat' => 'required|numeric|between:-90,90',
            'destino.lng' => 'required|numeric|between:-180,180',
            'destino.endereco' => 'required|string|max:255',
            'descricaoItem' => 'required|string|max:255',
            'pesoEstimado' => 'required|numeric|min:0|max:999.99',
            'dimensoes.largura' => 'required|integer|min:1|max:999',
            'dimensoes.altura' => 'required|integer|min:1|max:999',
            'dimensoes.profundidade' => 'required|integer|min:1|max:999',
            'tipo' => 'required|in:normal,expressa,sustentavel'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Debug para verificar o token recebido
        $bearerToken = $request->bearerToken();
        echo "\nToken recebido em store: " . $bearerToken;
        
        // Verificar o token manualmente
        $tokenParts = explode('|', $bearerToken);
        $tokenId = $tokenParts[0];
        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);
        
        if ($token) {
            $tokenUser = $token->tokenable;
            echo "\nToken ID: " . $token->id;
            echo "\nToken User ID: " . $token->tokenable_id;
            echo "\nToken User Tipo: " . $tokenUser->tipo;
            echo "\nToken Abilities: " . json_encode($token->abilities);
            
            // Usar o usuário do token diretamente
            $user = $tokenUser;
        } else {
            $user = $request->user();
        }
        
        echo "\nTipo do usuário criando entrega: " . $user->tipo;
        echo "\nID do usuário: " . $user->id;

        $valor = $this->calculateDeliveryValue($request);
        $tempoEstimado = $this->calculateEstimatedTime($request);

        $delivery = Delivery::create([
            'clienteId' => $user->id,
            'origem' => $request->origem,
            'destino' => $request->destino,
            'descricaoItem' => $request->descricaoItem,
            'pesoEstimado' => $request->pesoEstimado,
            'dimensoes' => $request->dimensoes,
            'tipo' => $request->tipo,
            'status' => 'pendente',
            'valor' => $valor,
            'tempoEstimado' => $tempoEstimado,
            'codigoConfirmacao' => strtoupper(substr(md5(uniqid()), 0, 6))
        ]);

        return response()->json([
            'id' => $delivery->id,
            'valor' => $valor,
            'tempoEstimado' => $tempoEstimado,
            'codigoConfirmacao' => $delivery->codigoConfirmacao,
            'status' => 'pendente',
            'clienteId' => $user->id
        ], 201);
    }

    public function accept(Request $request, string $id)
    {
        $delivery = Delivery::findOrFail($id);

        if ($delivery->status !== 'pendente') {
            return response()->json([
                'message' => 'Esta entrega já foi aceita'
            ], 409);
        }

        // Debug para verificar o token recebido
        $bearerToken = $request->bearerToken();
        echo "\nToken recebido: " . $bearerToken;
        
        // Verificar o token manualmente
        $tokenParts = explode('|', $bearerToken);
        $tokenId = $tokenParts[0];
        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);
        
        if ($token) {
            $tokenUser = $token->tokenable;
            echo "\nToken ID: " . $token->id;
            echo "\nToken User ID: " . $token->tokenable_id;
            echo "\nToken User Tipo: " . $tokenUser->tipo;
            echo "\nToken Abilities: " . json_encode($token->abilities);
            
            // Usar o usuário do token diretamente
            $user = $tokenUser;
        } else {
            $user = $request->user();
        }
        
        echo "\nTipo do usuário tentando aceitar: " . $user->tipo;
        echo "\nID do usuário: " . $user->id;
        
        if ($user->tipo !== 'entregador') {
            return response()->json([
                'message' => 'Apenas entregadores podem aceitar entregas'
            ], 403);
        }

        $delivery->update([
            'entregadorId' => (string)$user->id,
            'status' => 'aceito'
        ]);

        return response()->json([
            'id' => $delivery->id,
            'status' => $delivery->status,
            'entregador' => [
                'id' => (string)$user->id,
                'nome' => $user->nome,
                'fotoUrl' => $user->fotoUrl
            ]
        ], 200);
    }

    public function updateStatus(Request $request, string $id)
    {
        $validStatuses = ['coletado', 'em_transito', 'em_transporte', 'entregue'];
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:' . implode(',', $validStatuses),
            'codigoConfirmacao' => 'sometimes|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $delivery = Delivery::findOrFail($id);
        
        // Debug para verificar o token recebido
        $bearerToken = $request->bearerToken();
        echo "\nToken recebido em updateStatus: " . $bearerToken;
        
        // Verificar o token manualmente
        $tokenParts = explode('|', $bearerToken);
        $tokenId = $tokenParts[0];
        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);
        
        if ($token) {
            $tokenUser = $token->tokenable;
            echo "\nToken ID: " . $token->id;
            echo "\nToken User ID: " . $token->tokenable_id;
            echo "\nToken User Tipo: " . $tokenUser->tipo;
            echo "\nToken Abilities: " . json_encode($token->abilities);
            
            // Usar o usuário do token diretamente
            $user = $tokenUser;
        } else {
            $user = $request->user();
        }
        
        echo "\nTipo do usuário tentando atualizar: " . $user->tipo;
        echo "\nID do usuário: " . $user->id;
        echo "\nID do entregador da entrega: " . $delivery->entregadorId;
        
        // Verificar se o entregador é o mesmo que aceitou a entrega (comparando como strings)
        if ((string)$delivery->entregadorId !== (string)$user->id) {
            return response()->json(['message' => 'Apenas o entregador designado pode atualizar o status'], 403);
        }

        $updateData = ['status' => $request->status];
        if ($request->has('posicaoAtual')) {
            $updateData['posicaoAtual'] = $request->posicaoAtual;
        }

        $delivery->update($updateData);

        return response()->json($delivery, 200);
    }

    public function show(Request $request, string $id)
    {
        $delivery = Delivery::with(['cliente', 'entregador'])->findOrFail($id);

        // Debug para verificar o token recebido
        $bearerToken = $request->bearerToken();
        echo "\nToken recebido em show: " . $bearerToken;
        
        // Verificar o token manualmente
        $tokenParts = explode('|', $bearerToken);
        $tokenId = $tokenParts[0];
        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);
        
        if ($token) {
            $tokenUser = $token->tokenable;
            echo "\nToken ID: " . $token->id;
            echo "\nToken User ID: " . $token->tokenable_id;
            echo "\nToken User Tipo: " . $tokenUser->tipo;
            echo "\nToken Abilities: " . json_encode($token->abilities);
            
            // Usar o usuário do token diretamente
            $user = $tokenUser;
        } else {
            $user = $request->user();
        }
        
        echo "\nTipo do usuário tentando visualizar: " . $user->tipo;
        echo "\nID do usuário: " . $user->id;

        // Verificar se o usuário tem permissão para ver esta entrega
        if ($user->id != $delivery->clienteId && $user->id != $delivery->entregadorId) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        return response()->json([
            'id' => $delivery->id,
            'status' => $delivery->status,
            'entregador' => $delivery->entregador ? [
                'id' => (string)$delivery->entregador->id,
                'nome' => $delivery->entregador->nome,
                'fotoUrl' => $delivery->entregador->fotoUrl
            ] : null,
            'posicaoAtual' => $delivery->posicaoAtual,
            'historicoStatus' => $delivery->historicoStatus
        ]);
    }

    private function calculateDeliveryValue(Request $request): float
    {
        // Lógica simplificada para cálculo do valor
        $baseValue = 10.0;
        $distanceFactor = 0.5;
        $weightFactor = $request->pesoEstimado * 0.1;
        $typeFactor = match($request->tipo) {
            'expressa' => 1.5,
            'sustentavel' => 1.2,
            default => 1.0
        };

        return round($baseValue + $distanceFactor + $weightFactor * $typeFactor, 2);
    }

    private function calculateEstimatedTime(Request $request): int
    {
        // Lógica simplificada para cálculo do tempo estimado (em minutos)
        return match($request->tipo) {
            'expressa' => 30,
            'sustentavel' => 90,
            default => 60
        };
    }
}
