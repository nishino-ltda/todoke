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
        if ($user->type === 'customer') {
            $query->where('customer_id', $user->id);
        } elseif ($user->type === 'courrier') {
            $query->where('courrier_id', $user->id);
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
            'item_description' => 'required|string|max:255',
            'estimated_weight' => 'required|numeric|min:0|max:999.99',
            'dimensoes.largura' => 'required|integer|min:1|max:999',
            'dimensoes.altura' => 'required|integer|min:1|max:999',
            'dimensoes.profundidade' => 'required|integer|min:1|max:999',
            'type' => 'required|in:normal,expressa,sustentavel'
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
            'customer_id' => $user->id,
            'origem' => $request->origem,
            'destino' => $request->destino,
            'item_description' => $request->descricaoItem,
            'estimated_weight' => $request->pesoEstimado,
            'dimensions' => $request->dimensoes,
            'type' => $request->type,
            'status' => 'pending',
            'value' => $valor,
            'estimated_time' => $tempoEstimado,
            'confirmation_code' => strtoupper(substr(md5(uniqid()), 0, 6))
        ]);

        return response()->json([
            'id' => $delivery->id,
            'value' => $valor,
            'estimated_time' => $tempoEstimado,
            'confirmation_code' => $delivery->codigoConfirmacao,
            'status' => 'pending',
            'customer_id' => $user->id
        ], 201);
    }

    public function accept(Request $request, string $id)
    {
        $delivery = Delivery::findOrFail($id);

        if ($delivery->status !== 'pending') {
            return response()->json(['message' => 'Esta entrega já foi aceita'], 409);
        }

        // Retrieve the bearer token and determine the user
        $bearerToken = $request->bearerToken();
        $tokenParts = explode('|', $bearerToken);
        $tokenId = $tokenParts[0];
        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);
        $user = $token ? $token->tokenable : $request->user();

        // Ensure only delivery personnel can accept deliveries
        if ($user->type !== 'courrier') {
            return response()->json(['message' => 'Apenas courrieres podem aceitar entregas'], 403);
        }

        // Update the delivery status
        $delivery->update([
            'courrier_id' => (string)$user->id,
            'status' => 'accepted'
        ]);

        // Create notification for client
        Notification::create([
            'user_id' => $delivery->clienteId,
            'type' => 'entrega_atualizada',
            'data' => [
                'delivery_id' => $delivery->id,
                'status' => 'accepted',
                'message' => 'Sua entrega foi aceita por um courrier'
            ]
        ]);

        return response()->json([
            'id' => $delivery->id,
            'status' => $delivery->status,
            'courrier' => [
                'id' => (string)$user->id,
                'name' => $user->name,
                'photoUrl' => $user->fotoUrl
            ]
        ], 200);
    }

    public function updateStatus(Request $request, string $id)
    {
        $validStatuses = ['coletado', 'em_transito', 'in_transit', 'delivered'];

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:' . implode(',', $validStatuses),
            'confirmation_code' => 'sometimes|string|size:6'
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
        if ((string)$delivery->courrierId !== (string)$user->id) {
            return response()->json(['message' => 'Apenas o courrier designado pode atualizar o status'], 403);
        }

        // Update the delivery status and position if provided
        $updateData = ['status' => $request->status];
        if ($request->has('current_position')) {
            $updateData['current_position'] = $request->posicaoAtual;
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
        $delivery = Delivery::with(['customer', 'courrier'])->findOrFail($id);

        // Retrieve the bearer token and determine the user
        $bearerToken = $request->bearerToken();
        $tokenParts = explode('|', $bearerToken);
        $tokenId = $tokenParts[0];
        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);
        $user = $token ? $token->tokenable : $request->user();

        // Ensure the user has permission to view the delivery
        if ($user->id != $delivery->clienteId && $user->id != $delivery->courrierId) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        return response()->json([
            'id' => $delivery->id,
            'status' => $delivery->status,
            'courrier' => $delivery->courrier ? [
                'id' => (string)$delivery->courrier->id,
                'name' => $delivery->courrier->name,
                'photoUrl' => $delivery->courrier->fotoUrl
            ] : null,
            'current_position' => $delivery->posicaoAtual,
            'status_history' => $delivery->historicoStatus
        ]);
    }

    private function calculateDeliveryValue(Request $request): float
    {
        // Simplified logic for calculating delivery value
        $baseValue = 10.0;
        $distanceFactor = 0.5;
        $weightFactor = $request->pesoEstimado * 0.1;
        $typeFactor = match($request->type) {
            'expressa' => 1.5,
            'sustentavel' => 1.2,
            default => 1.0
        };

        return round($baseValue + $distanceFactor + $weightFactor * $typeFactor, 2);
    }

    private function calculateEstimatedTime(Request $request): int
    {
        // Simplified logic for calculating estimated time (in minutes)
        return match($request->type) {
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
        if ($user->id != $delivery->clienteId && $user->id != $delivery->courrierId) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        // Validate the message
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Create and save the message
        $message = Message::create([
            'delivery_id' => $delivery->id,
            'user_id' => $user->id,
            'text' => $request->texto
        ]);

        return response()->json([
            'id' => $message->id,
            'text' => $message->texto,
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
        if ($user->id != $delivery->clienteId && $user->id != $delivery->courrierId) {
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
                    'text' => $message->texto,
                    'autor' => [
                        'id' => (string)$message->user->id,
                        'name' => $message->user->name
                    ]
                ];
            });

        return response()->json($messages);
    }
}
