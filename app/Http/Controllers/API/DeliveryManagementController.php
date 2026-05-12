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
        // Admins don't get filtered

        $limit = $request->input('limit', 15);
        $deliveries = $query->with(['customer', 'logisticsPartner', 'courier'])
                            ->orderBy('created_at', 'desc')
                            ->paginate($limit);

        return response()->json([
            'deliveries' => $deliveries->items(),
            'total' => $deliveries->total(),
            'per_page' => $deliveries->perPage(),
            'current_page' => $deliveries->currentPage(),
            'last_page' => $deliveries->lastPage()
        ]);
    }

    public function monitor(Request $request)
    {
        $bearerToken = $request->bearerToken();
        $user = $this->getUserFromToken($bearerToken, $request);

        if ($user->type !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Monitoring returns active deliveries with current positions
        $activeDeliveries = Delivery::whereNotIn('status', ['delivered', 'canceled', 'failed'])
            ->with(['customer', 'courier'])
            ->get();

        return response()->json([
            'data' => $activeDeliveries
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

        // Disparar evento de nova entrega disponível
        event(new \App\Events\NewDeliveryAvailable($delivery));

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
        $delivery = Delivery::with(['customer', 'logisticsPartner', 'courier'])->findOrFail($id);

        $bearerToken = $request->bearerToken();
        $user = $this->getUserFromToken($bearerToken, $request);

        $isAuthorized = $user->id == $delivery->customer_id || 
                        $user->id == $delivery->logistics_partner_id || 
                        $user->id == $delivery->courier_id ||
                        $user->type === 'admin';

        if (!$isAuthorized) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'id' => $delivery->id,
            'status' => $delivery->status,
            'logistics_partner' => $delivery->logisticsPartner ? [
                'id' => (string)$delivery->logisticsPartner->id,
                'name' => $delivery->logisticsPartner->name,
                'photoUrl' => $delivery->logisticsPartner->photo_url
            ] : null,
            'courier' => $delivery->courier ? [
                'id' => (string)$delivery->courier->id,
                'name' => $delivery->courier->name,
                'photoUrl' => $delivery->courier->photo_url
            ] : null,
            'current_position' => $delivery->current_position,
            'status_history' => $delivery->status_history,
            'origin' => $delivery->origin,
            'destination' => $delivery->destination,
            'item_description' => $delivery->item_description,
            'estimated_weight' => $delivery->estimated_weight,
            'dimensions' => $delivery->dimensions,
            'type' => $delivery->type,
            'value' => $delivery->value,
            'estimated_time' => $delivery->estimated_time,
            'stages' => $delivery->stages,
            'is_hybrid' => $delivery->is_hybrid
        ]);
    }

    public function available(Request $request)
    {
        $deliveries = Delivery::whereNull('courier_id')
            ->whereIn('status', ['pending'])
            ->with(['customer:id,name', 'logisticsPartner:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('limit', 20));

        return response()->json($deliveries);
    }
}
