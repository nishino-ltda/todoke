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
        } elseif ($user->type === 'courier') {
            $query->where('courier_id', $user->id);
        }

        // Paginate and return the deliveries
        $deliveries = $query->orderBy('createdAt', 'desc')->paginate(15);

        return response()->json([
            'deliveries' => $deliveries->items(),
            'total' => $deliveries->total(),
            'per_page' => $deliveries->perPage(),
            'current_page' => $deliveries->currentPage()
        ]);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'origin' => 'required|array',
            'origin.lat' => 'required|numeric|between:-90,90',
            'origin.lng' => 'required|numeric|between:-180,180',
            'origin.address' => 'required|string|max:255',
            'destination' => 'required|array',
            'destination.lat' => 'required|numeric|between:-180,180',
            'destination.lng' => 'required|numeric|between:-180,180',
            'destination.address' => 'required|string|max:255',
            'item_description' => 'required|string|max:255',
            'estimated_weight' => 'required|numeric|min:0|max:999.99',
            'dimensions' => 'required|array',
            'dimensions.width' => 'required|integer|min:1|max:999',
            'dimensions.height' => 'required|integer|min:1|max:999',
            'dimensions.depth' => 'required|integer|min:1|max:999',
            'type' => 'required|in:standard,express,priority'
        ], [
            'origin.required' => 'The origin field is required',
            'destination.required' => 'The destination field is required',
            'dimensions.required' => 'The dimensions field is required'
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
        $value = $this->calculateDeliveryValue($request);
        $estimatedTime = $this->calculateEstimatedTime($request);

        // Create a new delivery
        $delivery = Delivery::create([
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
            'item_description' => $request->item_description,
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
            'confirmation_code' => strtoupper(substr(md5(uniqid()), 0, 6))
        ]);

        return response()->json([
            'id' => $delivery->id,
            'value' => $value,
            'estimated_time' => $estimatedTime,
            'confirmation_code' => $delivery->confirmation_code,
            'status' => 'pending',
            'customer_id' => $user->id
        ], 201);
    }

    public function accept(Request $request, string $id)
    {
        $delivery = Delivery::findOrFail($id);

        if ($delivery->status !== 'pending') {
            return response()->json(['message' => 'This delivery has already been accepted'], 409);
        }

        // Retrieve the bearer token and determine the user
        $bearerToken = $request->bearerToken();
        $tokenParts = explode('|', $bearerToken);
        $tokenId = $tokenParts[0];
        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);
        $user = $token ? $token->tokenable : $request->user();

        // Ensure only delivery personnel can accept deliveries
        if ($user->type !== 'courier') {
            return response()->json(['message' => 'Only couriers can accept deliveries'], 403);
        }

        // Update the delivery status
        $delivery->update([
            'courier_id' => (string)$user->id,
            'status' => 'accepted'
        ]);

        // Create notification for client
        Notification::create([
            'user_id' => $delivery->customer_id,
            'type' => 'delivery_updated',
            'data' => [
                'delivery_id' => $delivery->id,
                'status' => 'accepted',
                'message' => 'Your delivery has been accepted by a courier'
            ]
        ]);

        return response()->json([
            'id' => $delivery->id,
            'status' => $delivery->status,
            'courier' => [
                'id' => (string)$user->id,
                'name' => $user->name,
                'photoUrl' => $user->photo_url
            ]
        ], 200);
    }

    public function updateStatus(Request $request, string $id)
    {
        $validStatuses = ['collected', 'in_transit', 'delivered'];

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
        if ((string)$delivery->courier_id !== (string)$user->id) {
            return response()->json(['message' => 'Only the assigned courier can update the status'], 403);
        }

        // Update the delivery status and position if provided
        $updateData = ['status' => $request->status];
        if ($request->has('current_position')) {
            $updateData['current_position'] = $request->current_position;
        }

        $delivery->update($updateData);

        // Create notification for client when status changes
        Notification::create([
            'user_id' => $delivery->customer_id,
            'type' => 'delivery_updated',
            'data' => [
                'delivery_id' => $delivery->id,
                'status' => $request->status,
                'message' => 'Your delivery status has been updated to: ' . $request->status
            ]
        ]);

        return response()->json($delivery, 200);
    }

    public function show(Request $request, string $id)
    {
        $delivery = Delivery::with(['customer', 'courier'])->findOrFail($id);

        // Retrieve the bearer token and determine the user
        $bearerToken = $request->bearerToken();
        $tokenParts = explode('|', $bearerToken);
        $tokenId = $tokenParts[0];
        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);
        $user = $token ? $token->tokenable : $request->user();

        // Ensure the user has permission to view the delivery
        if ($user->id != $delivery->customer_id && $user->id != $delivery->courier_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'id' => $delivery->id,
            'status' => $delivery->status,
            'courier' => $delivery->courier ? [
                'id' => (string)$delivery->courier->id,
                'name' => $delivery->courier->name,
                'photoUrl' => $delivery->courier->photo_url
            ] : null,
            'current_position' => $delivery->current_position,
            'status_history' => $delivery->status_history
        ]);
    }

    private function calculateDeliveryValue(Request $request): float
    {
        // Simplified logic for calculating delivery value
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
        // Simplified logic for calculating estimated time (in minutes)
        return match($request->type) {
            'express' => 30,
            'priority' => 20,
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
        if ($user->id != $delivery->customer_id && $user->id != $delivery->courier_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
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

        // Retrieve the bearer token and determine the user
        $bearerToken = $request->bearerToken();
        $tokenParts = explode('|', $bearerToken);
        $tokenId = $tokenParts[0];
        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);
        $user = $token ? $token->tokenable : $request->user();

        // Ensure the user is either the client or delivery person
        if ($user->id != $delivery->customer_id && $user->id != $delivery->courier_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Retrieve messages with user data
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
}
