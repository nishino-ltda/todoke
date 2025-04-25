<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Message;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        // Retrieve the bearer token from the request
        $bearerToken = $request->bearerToken();

        // Manually verify the token
        $tokenParts = explode('|', $bearerToken);
        $tokenId = $tokenParts[0];
        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);

        // Determine the user based on the token
        $user = $token ? $token->tokenable : $request->user();

        // Filter deliveries based on user type
        $query = Delivery::query();
        if ($user->tipo === 'cliente') {
            $query->where('clienteId', $user->id);
        } elseif ($user->tipo === 'entregador') {
            $query->where('entregadorId', $user->id);
        }

        // Paginate and return the deliveries
        $deliveries = $query->orderBy('createdAt', 'desc')->paginate(15);

        return response()->json([
            'entregas' => $deliveries->items(),
            'total' => $deliveries->total(),
            'per_page' => $deliveries->perPage(),
            'current_page' => $deliveries->currentPage()
        ]);
    }

    public function store(Request $request)
    {
        // Validate the request data
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

        // Retrieve the bearer token and determine the user
        $bearerToken = $request->bearerToken();
        $tokenParts = explode('|', $bearerToken);
        $tokenId = $tokenParts[0];
        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);
        $user = $token ? $token->tokenable : $request->user();

        // Calculate delivery value and estimated time
        $valor = $this->calculateDeliveryValue($request);
        $tempoEstimado = $this->calculateEstimatedTime($request);

        // Create a new delivery
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
            return response()->json(['message' => 'Esta entrega já foi aceita'], 409);
        }

        // Retrieve the bearer token and determine the user
        $bearerToken = $request->bearerToken();
        $tokenParts = explode('|', $bearerToken);
        $tokenId = $tokenParts[0];
        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);
        $user = $token ? $token->tokenable : $request->user();

        // Ensure only delivery personnel can accept deliveries
        if ($user->tipo !== 'entregador') {
            return response()->json(['message' => 'Apenas entregadores podem aceitar entregas'], 403);
        }

        // Update the delivery status
        $delivery->update([
            'entregadorId' => (string)$user->id,
            'status' => 'aceito'
        ]);

        // Create notification for client
        Notification::create([
            'user_id' => $delivery->clienteId,
            'type' => 'entrega_atualizada',
            'data' => [
                'delivery_id' => $delivery->id,
                'status' => 'aceito',
                'message' => 'Sua entrega foi aceita por um entregador'
            ]
        ]);

        return response()->json([
            'id' => $delivery->id,
            'status' => $delivery->status,
            'entregador' => [
                'id' => (string)$user->id,
                'name' => $user->name,
                'fotoUrl' => $user->fotoUrl
            ]
        ], 200);
    }

    public function updateStatus(Request $request, string $id)
    {
        $validStatuses = ['coletado', 'em_transito', 'em_transporte', 'entregue'];

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:' . implode(',', $validStatuses),
            'codigoConfirmacao' => 'sometimes|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $delivery = Delivery::findOrFail($id);

        // Retrieve the bearer token and determine the user
        $bearerToken = $request->bearerToken();
        $tokenParts = explode('|', $bearerToken);
        $tokenId = $tokenParts[0];
        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);
        $user = $token ? $token->tokenable : $request->user();

        // Ensure only the assigned delivery personnel can update the status
        if ((string)$delivery->entregadorId !== (string)$user->id) {
            return response()->json(['message' => 'Apenas o entregador designado pode atualizar o status'], 403);
        }

        // Update the delivery status and position if provided
        $updateData = ['status' => $request->status];
        if ($request->has('posicaoAtual')) {
            $updateData['posicaoAtual'] = $request->posicaoAtual;
        }

        $delivery->update($updateData);

        // Create notification for client when status changes
        Notification::create([
            'user_id' => $delivery->clienteId,
            'type' => 'entrega_atualizada',
            'data' => [
                'delivery_id' => $delivery->id,
                'status' => $request->status,
                'message' => 'Status da sua entrega foi atualizado para: ' . $request->status
            ]
        ]);

        return response()->json($delivery, 200);
    }

    public function show(Request $request, string $id)
    {
        $delivery = Delivery::with(['cliente', 'entregador'])->findOrFail($id);

        // Retrieve the bearer token and determine the user
        $bearerToken = $request->bearerToken();
        $tokenParts = explode('|', $bearerToken);
        $tokenId = $tokenParts[0];
        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);
        $user = $token ? $token->tokenable : $request->user();

        // Ensure the user has permission to view the delivery
        if ($user->id != $delivery->clienteId && $user->id != $delivery->entregadorId) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        return response()->json([
            'id' => $delivery->id,
            'status' => $delivery->status,
            'entregador' => $delivery->entregador ? [
                'id' => (string)$delivery->entregador->id,
                'name' => $delivery->entregador->name,
                'fotoUrl' => $delivery->entregador->fotoUrl
            ] : null,
            'posicaoAtual' => $delivery->posicaoAtual,
            'historicoStatus' => $delivery->historicoStatus
        ]);
    }

    private function calculateDeliveryValue(Request $request): float
    {
        // Simplified logic for calculating delivery value
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
        // Simplified logic for calculating estimated time (in minutes)
        return match($request->tipo) {
            'expressa' => 30,
            'sustentavel' => 90,
            default => 60
        };
    }

    public function storeMessage(Request $request, string $id)
    {
        $delivery = Delivery::findOrFail($id);

        // Retrieve the bearer token and determine the user
        $bearerToken = $request->bearerToken();
        $tokenParts = explode('|', $bearerToken);
        $tokenId = $tokenParts[0];
        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);
        $user = $token ? $token->tokenable : $request->user();

        // Ensure the user is either the client or delivery person
        if ($user->id != $delivery->clienteId && $user->id != $delivery->entregadorId) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        // Validate the message
        $validator = Validator::make($request->all(), [
            'texto' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Create and save the message
        $message = Message::create([
            'delivery_id' => $delivery->id,
            'user_id' => $user->id,
            'texto' => $request->texto
        ]);

        return response()->json([
            'id' => $message->id,
            'texto' => $message->texto,
            'autor' => [
                'id' => (string)$user->id,
                'name' => $user->name
            ]
        ], 201);
    }

    public function indexMessages(Request $request, string $id)
    {
        $delivery = Delivery::findOrFail($id);

        // Retrieve the bearer token and determine the user
        $bearerToken = $request->bearerToken();
        $tokenParts = explode('|', $bearerToken);
        $tokenId = $tokenParts[0];
        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);
        $user = $token ? $token->tokenable : $request->user();

        // Ensure the user is either the client or delivery person
        if ($user->id != $delivery->clienteId && $user->id != $delivery->entregadorId) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        // Retrieve messages with user data
        $messages = Message::with('user')
            ->where('delivery_id', $delivery->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'texto' => $message->texto,
                    'autor' => [
                        'id' => (string)$message->user->id,
                        'name' => $message->user->name
                    ]
                ];
            });

        return response()->json($messages);
    }
}
