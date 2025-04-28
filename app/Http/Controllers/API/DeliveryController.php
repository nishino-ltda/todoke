<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Message;
use App\Models\Notification;
use App\Services\DeliveryStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DeliveryController extends Controller
{
    public function __construct(
        private DeliveryStatusService $statusService
    ) {}

    // ===========================
    // Delivery Management Methods
    // ===========================

    public function index(Request $request)
    {
        $bearerToken = $request->bearerToken();
        $user = $this->getUserFromToken($bearerToken, $request);

        $query = Delivery::query();
        if ($user->type === 'customer') {
            $query->where('customer_id', $user->id);
        } elseif ($user->type === 'partner') {
            $query->where('logistics_partner_id', $user->id);
        } elseif ($user->type === 'courier') {
            $query->where('courier_id', $user->id);
        }

        $limit = $request->input('limit', 15);
        $deliveries = $query->orderBy('created_at', 'desc')->paginate($limit);

        return response()->json([
            'deliveries' => $deliveries->items(),
            'total' => $deliveries->total(),
            'per_page' => $deliveries->perPage(),
            'current_page' => $deliveries->currentPage()
        ]);
    }

    public function store(Request $request)
    {
        $validator = $this->validateDeliveryRequest($request);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $bearerToken = $request->bearerToken();
        $user = $this->getUserFromToken($bearerToken, $request);

        $value = $this->calculateDeliveryValue($request);
        $estimatedTime = $this->calculateEstimatedTime($request);

        $deliveryData = $this->prepareDeliveryData($request, $user, $value, $estimatedTime);
        $delivery = Delivery::create($deliveryData);

        // Criar assignments se existirem
        if (isset($deliveryData['assignments'])) {
            foreach ($deliveryData['assignments'] as $assignmentData) {
                $assignmentData['delivery_id'] = $delivery->id;
                \App\Models\DeliveryAssignment::create($assignmentData);
            }
        }

        if ($delivery->logistics_partner_id) {
            Notification::create([
                'user_id' => $delivery->logistics_partner_id,
                'type' => 'delivery_request',
                'data' => [
                    'delivery_id' => $delivery->id,
                    'message' => 'New delivery request from customer'
                ]
            ]);
        }

        return response()->json($this->prepareDeliveryResponse($delivery, $user, $value, $estimatedTime), 201);
    }

    public function show(Request $request, string $id)
    {
        $delivery = Delivery::with(['customer', 'logisticsPartner'])->findOrFail($id);

        $bearerToken = $request->bearerToken();
        $user = $this->getUserFromToken($bearerToken, $request);

        if ($user->id != $delivery->customer_id && $user->id != $delivery->logistics_partner_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'id' => $delivery->id,
            'status' => $delivery->status,
            'logistics_partner' => $delivery->courier ? [
                'id' => (string)$delivery->courier->id,
                'name' => $delivery->courier->name,
                'photoUrl' => $delivery->courier->photo_url
            ] : null,
            'current_position' => $delivery->current_position,
            'status_history' => $delivery->status_history
        ]);
    }

    // ===========================
    // Delivery Status Methods
    // ===========================

    public function accept(Request $request, string $id)
    {
        $delivery = Delivery::findOrFail($id);

        $bearerToken = $request->bearerToken();
        $user = $this->getUserFromToken($bearerToken, $request);

        if ($user->type !== 'courier') {
            return response()->json(['message' => 'Only couriers can accept deliveries'], 403);
        }

        try {
            $delivery = $this->statusService->acceptDelivery($delivery, (string)$user->id);

            return response()->json([
                'id' => $delivery->id,
                'status' => $delivery->status,
                'logistics_partner' => [
                    'id' => (string)$user->id,
                    'name' => $user->name,
                    'photoUrl' => $user->photo_url
                ]
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function updateStatus(Request $request, string $id)
    {
        $validStatuses = ['collected', 'in_transit', 'delivered', 'canceled', 'drone_returned', 'failed'];

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:' . implode(',', $validStatuses),
            'confirmation_code' => 'sometimes|string|size:6',
            'current_position' => 'sometimes|array',
            'stage_type' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $delivery = Delivery::findOrFail($id);

        $bearerToken = $request->bearerToken();
        $user = $this->getUserFromToken($bearerToken, $request);

        $isAssignedCourier = (string)$delivery->courier_id === (string)$user->id;
        $isLogisticsPartner = (string)$delivery->logistics_partner_id === (string)$user->id;

        if (!$isAssignedCourier && !$isLogisticsPartner) {
            return response()->json(['message' => 'Only the assigned courier or logistics partner can update the status'], 403);
        }

        try {
            $delivery = $this->statusService->updateStatus($delivery, $request->all());
            
            // Format the response to match the expected structure in tests
            return response()->json([
                'message' => 'Delivery status updated successfully',
                'delivery' => $delivery
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // ===========================
    // Messaging Methods
    // ===========================

    public function storeMessage(Request $request, string $id)
    {
        $delivery = Delivery::findOrFail($id);

        $bearerToken = $request->bearerToken();
        $user = $this->getUserFromToken($bearerToken, $request);

        if ($user->id != $delivery->customer_id && 
            $user->id != $delivery->courier_id && 
            $user->id != $delivery->logistics_partner_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $message = Message::create([
            'delivery_id' => $delivery->id,
            'user_id' => $user->id,
            'text' => $request->text
        ]);

        return response()->json([
            'id' => $message->id,
            'text' => $message->text,
            'author' => [
                'id' => (string)$user->id,
                'name' => $user->name
            ]
        ], 201);
    }

    public function indexMessages(Request $request, string $id)
    {
        $delivery = Delivery::findOrFail($id);

        $bearerToken = $request->bearerToken();
        $user = $this->getUserFromToken($bearerToken, $request);

        if ($user->id != $delivery->customer_id && 
            $user->id != $delivery->courier_id && 
            $user->id != $delivery->logistics_partner_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = Message::with('user')
            ->where('delivery_id', $delivery->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'text' => $message->text,
                    'author' => [
                        'id' => (string)$message->user->id,
                        'name' => $message->user->name
                    ]
                ];
            });

        return response()->json($messages);
    }

    // ===========================
    // Helper Methods
    // ===========================

    private function getUserFromToken(?string $bearerToken, Request $request)
    {
        if ($bearerToken) {
            $tokenParts = explode('|', $bearerToken);
            $tokenId = $tokenParts[0] ?? null;
            if ($tokenId) {
                $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);
                if ($token) {
                    return $token->tokenable;
                }
            }
        }
        
        $user = $request->user();
        if (!$user) {
            throw new \RuntimeException('No authenticated user found');
        }
        return $user;
    }

    private function validateDeliveryRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'origin' => 'required|array',
            'origin.lat' => 'required|numeric|between:-90,90',
            'origin.lng' => 'required|numeric|between:-180,180',
            'origin.address' => 'required|string|max:255',
            'destination' => 'required|array',
            'destination.lat' => 'required|numeric|between:-180,180',
            'destination.lng' => 'required|numeric|between:-180,180',
            'destination.address' => 'required|string|max:255',
            'products' => 'sometimes|array',
            'products.*.name' => 'required_with:products|string|max:255',
            'products.*.quantity' => 'required_with:products|integer|min:1',
            'item_description' => 'required_without:products|string|max:255',
            'estimated_weight' => 'required|numeric|min:0|max:999.99',
            'dimensions' => 'required|array',
            'dimensions.width' => 'required|integer|min:1|max:999',
            'dimensions.height' => 'required|integer|min:1|max:999',
            'dimensions.depth' => 'required|integer|min:1|max:999',
            'type' => 'required|in:standard,express,priority',
            'courier_id' => 'sometimes|string|exists:users,id',
            'isHybrid' => 'sometimes|boolean',
            'special_instructions' => 'sometimes|string|max:500',
            'payment_method' => 'required|in:credit_card,debit_card,pix,cash,voucher'
        ], [
            'origin.required' => 'The origin field is required',
            'destination.required' => 'The destination field is required',
            'dimensions.required' => 'The dimensions field is required'
        ]);
    }

    private function prepareDeliveryData(Request $request, $user, float $value, int $estimatedTime): array
    {
        $deliveryData = [
            'customer_id' => $user->id,
            'origin' => [
                'lat' => $request->origin['lat'],
                'lng' => $request->origin['lng'],
                'address' => $request->origin['address']
            ],
            'destination' => [
                'lat' => $request->destination['lat'],
                'lng' => $request->destination['lng'],
                'address' => $request->destination['address']
            ],
            'estimated_weight' => $request->estimated_weight,
            'dimensions' => [
                'width' => $request->dimensions['width'],
                'height' => $request->dimensions['height'],
                'depth' => $request->dimensions['depth']
            ],
            'type' => $request->type,
            'status' => 'pending',
            'value' => $value,
            'estimated_time' => $estimatedTime,
            'confirmation_code' => strtoupper(substr(md5(uniqid()), 0, 6)),
            'special_instructions' => $request->special_instructions,
            'payment_method' => $request->payment_method
        ];

        if (isset($request->products)) {
            $deliveryData['item_description'] = implode(', ', array_map(
                fn($p) => "{$p['quantity']}x {$p['name']}", 
                $request->products
            ));
        } else {
            $deliveryData['item_description'] = $request->item_description;
        }

        if (isset($request->logistics_partner_id)) {
            $deliveryData['logistics_partner_id'] = $request->logistics_partner_id;
        }

        if (isset($request->courier_id)) {
            $deliveryData['courier_id'] = $request->courier_id;
        }

        if ($request->isHybrid) {
            // Buscar nodes apropriados para cada etapa
            $motoboyNode = null;
            $droneNode = null;
            
            // Buscar nodes ativos para cada tipo
            $motoboyNode = \App\Models\Node::where('type', 'delivery_point')
                ->where('status', 'active')
                ->first();

            $droneNode = \App\Models\Node::where('type', 'distribution_center')
                ->where('status', 'active')
                ->first();

            Log::debug('Found nodes for hybrid delivery', [
                'motoboy_node' => $motoboyNode ? $motoboyNode->toArray() : null,
                'drone_node' => $droneNode ? $droneNode->toArray() : null
            ]);
            
            // Sempre criar stages mesmo sem nodes encontrados
            $deliveryData['stages'] = [
                [
                    'type' => 'delivery_point',
                    'status' => 'pending',
                    'partner_id' => $motoboyNode ? $motoboyNode->partner_id : null,
                    'node_id' => $motoboyNode ? $motoboyNode->id : null
                ],
                [
                    'type' => 'distribution_center',
                    'status' => 'pending',
                    'partner_id' => $droneNode ? $droneNode->partner_id : null,
                    'node_id' => $droneNode ? $droneNode->id : null
                ]
            ];
            
            // Definir o parceiro logístico inicial como o primeiro stage
            $deliveryData['logistics_partner_id'] = $deliveryData['stages'][0]['partner_id'];
            
            // Criar assignments automaticamente para cada stage
            $deliveryData['assignments'] = [
                [
                    'partner_id' => $deliveryData['stages'][0]['partner_id'],
                    'stage' => 1,
                    'status' => 'pending'
                ],
                [
                    'partner_id' => $deliveryData['stages'][1]['partner_id'],
                    'stage' => 2,
                    'status' => 'pending'
                ]
            ];
            
            Log::info('Setting hybrid delivery stages and assignments', [
                'stages' => $deliveryData['stages'],
                'assignments' => $deliveryData['assignments']
            ]);
        } else {
            $deliveryData['stages'] = null;
        }

        return $deliveryData;
    }

    private function prepareDeliveryResponse($delivery, $user, float $value, int $estimatedTime): array
    {
        $response = [
            'id' => $delivery->id,
            'status' => $delivery->status,
            'customer_id' => $user->id,
            'value' => $value,
            'estimated_time' => $estimatedTime,
            'confirmation_code' => $delivery->confirmation_code,
            'special_instructions' => $delivery->special_instructions,
            'payment_method' => $delivery->payment_method
        ];

        if ($delivery->logistics_partner_id) {
            $response['logistics_partner'] = [
                'id' => (string)$delivery->logistics_partner_id,
                'name' => \App\Models\User::find($delivery->logistics_partner_id)->name
            ];
        }

        if ($delivery->stages) {
            $response['stages'] = $delivery->stages;
        }

        return $response;
    }

    private function calculateDeliveryValue(Request $request): float
    {
        $baseValue = 10.0;
        $distanceFactor = 0.5;
        $weightFactor = $request->estimated_weight * 0.1;
        $typeFactor = match($request->type) {
            'express' => 1.5,
            'priority' => 1.8,
            default => 1.0
        };

        return round($baseValue + $distanceFactor + $weightFactor * $typeFactor, 2);
    }

    private function calculateEstimatedTime(Request $request): int
    {
        return match($request->type) {
            'express' => 30,
            'priority' => 20,
            default => 60
        };
    }
}
