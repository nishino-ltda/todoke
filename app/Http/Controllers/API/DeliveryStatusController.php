<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\DeliveryHelpersTrait;
use App\Services\DeliveryStatusService;

class DeliveryStatusController extends Controller
{
    use DeliveryHelpersTrait;

    public function __construct(
        private DeliveryStatusService $statusService
    ) {}

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
            
            return response()->json([
                'id' => $delivery->id,
                'status' => $delivery->status,
                'message' => 'Delivery status updated successfully',
                'delivery' => $delivery
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
