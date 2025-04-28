<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\API\DeliveryHelpersTrait;

class DeliveryManagementController extends Controller
{
    use DeliveryHelpersTrait;

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

        // Create assignments if they exist
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
}
