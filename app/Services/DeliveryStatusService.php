<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\DeliveryAssignment;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class DeliveryStatusService
{
    private const VALID_STATUSES = [
        'pending',
        'accepted',
        'collected',
        'in_transit',
        'delivered',
        'canceled',
        // Novos status específicos para drones
        'drone_launched',
        'drone_in_route',
        'drone_arrived',
        'drone_returned'
    ];

    private const VALID_STAGE_TYPES = [
        'delivery_point',
        'distribution_center'
    ];

    /**
     * Accept a delivery by a courier
     */
    public function acceptDelivery(Delivery $delivery, string $courierId): Delivery
    {
        if ($delivery->status !== 'pending') {
            throw new \InvalidArgumentException('Delivery has already been accepted');
        }

        $delivery->update([
            'courier_id' => $courierId,
            'status' => 'accepted'
        ]);

        // Create assignments for hybrid deliveries
        if ($delivery->stages && count($delivery->stages) > 0) {
            $this->createAssignments($delivery);
        }

        $this->createNotification(
            $delivery->customer_id,
            'delivery_updated',
            [
                'delivery_id' => $delivery->id,
                'status' => 'accepted',
                'message' => 'Your delivery has been accepted by a courier'
            ]
        );

        return $delivery;
    }

    /**
     * Create assignments for hybrid delivery stages
     */
    private function createAssignments(Delivery $delivery): void
    {
        foreach ($delivery->stages as $index => $stage) {
            $partnerId = $stage['partner_id'] ?? $delivery->logistics_partner_id;
            
            if (!$partnerId) {
                continue;
            }
            
            DeliveryAssignment::updateOrCreate([
                'delivery_id' => $delivery->id,
                'stage' => $index + 1
            ], [
                'partner_id' => $partnerId,
                'status' => 'pending'
            ]);
        }
    }

    /**
     * Update delivery status with validation
     */
    public function updateStatus(Delivery $delivery, array $data): Delivery
    {
        $status = $data['status'];
        $stageType = $data['stage_type'] ?? null;
        $position = $data['current_position'] ?? null;

        if (!in_array($status, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException('Invalid status');
        }

        // For hybrid deliveries with stages
        if ($delivery->stages && $stageType) {
            if (!in_array($stageType, self::VALID_STAGE_TYPES)) {
                throw new \InvalidArgumentException('Invalid stage type');
            }

            return $this->updateStageStatus($delivery, $status, $stageType, $position);
        }

        // For simple deliveries
        $delivery->update([
            'status' => $status,
            'current_position' => $position
        ]);

        $this->createNotification(
            $delivery->customer_id,
            'delivery_updated',
            [
                'delivery_id' => $delivery->id,
                'status' => $status,
                'message' => "Your delivery status has been updated to: $status"
            ]
        );

        return $delivery;
    }

    /**
     * Handle status updates for hybrid deliveries with stages
     */
    private function updateStageStatus(Delivery $delivery, string $status, string $stageType, ?array $position): Delivery
    {
        $stages = $delivery->stages;
        $allStagesComplete = true;

        // Update the specific stage status
        foreach ($stages as &$stage) {
            if ($stage['type'] === $stageType) {
                $stage['status'] = $status;
            }

            if ($stage['status'] !== 'delivered') {
                $allStagesComplete = false;
            }
        }

        // Force delivered status if all stages are delivered
        if ($allStagesComplete) {
            $status = 'delivered';
        }

        $updateData = [
            'stages' => $stages,
            'current_position' => $position
        ];

        // Update overall status based on stage status
        if ($allStagesComplete) {
            $updateData['status'] = 'delivered';
        } else if ($status === 'canceled') {
            // If any stage is canceled, the whole delivery is canceled
            $updateData['status'] = 'canceled';
            
            // Update all other stages to canceled as well (cascading cancellation)
            foreach ($stages as $index => &$otherStage) {
                if ($otherStage['type'] !== $stageType && 
                    $otherStage['status'] !== 'delivered' && 
                    $otherStage['status'] !== 'completed') {
                    $otherStage['status'] = 'canceled';
                    
                    // Also update the corresponding assignment
                    $otherStageNumber = $index;
                    DeliveryAssignment::where('delivery_id', $delivery->id)
                        ->where('stage', $otherStageNumber)
                        ->update(['status' => 'canceled']);
                }
            }
            $updateData['stages'] = $stages;
        } else if ($status === 'failed' || $status === 'drone_returned') {
            // If drone fails or returns, mark the delivery as failed
            $updateData['status'] = 'failed';
        } else {
            // For drone statuses, maintain the overall status as in_transit
            if (in_array($status, ['drone_launched', 'drone_in_route', 'drone_arrived'])) {
                $updateData['status'] = 'in_transit';
            } else {
                $updateData['status'] = $status;
            }
        }

        $delivery->update($updateData);

        // Update the corresponding delivery assignment
        $stageIndex = array_search($stageType, array_column($stages, 'type'));
        $stageNumber = $stageIndex;
        Log::debug('Updating assignment status', [
            'delivery_id' => $delivery->id,
            'stage_type' => $stageType,
            'stage_number' => $stageNumber,
            'status' => $status,
            'stages' => $stages,
            'stage_types' => array_column($stages, 'type')
        ]);

        // Map stage status to assignment status
        $assignmentStatus = match($status) {
            'collected' => 'collected',
            'in_transit' => 'in_transit',
            'delivered' => 'delivered',
            'canceled' => 'canceled',
            'failed' => 'failed',
            'drone_launched' => 'in_transit', // Map drone statuses to in_transit for assignments
            'drone_in_route' => 'in_transit',
            'drone_arrived' => 'in_transit',
            'drone_returned' => 'failed', // Map drone_returned to failed for assignments
            default => $status
        };

        // Always update assignment status based on both stage status and delivery status
        $finalStatus = ($status === 'delivered' || $updateData['status'] === 'delivered') 
            ? 'delivered' 
            : (($status === 'canceled' || $updateData['status'] === 'canceled')
                ? 'canceled'
                : (($status === 'failed' || $status === 'drone_returned' || $updateData['status'] === 'failed')
                    ? 'failed'
                    : $assignmentStatus));

        // Force update to delivered/canceled/failed/in_transit status if that's what was requested
        if (in_array($status, ['delivered', 'canceled', 'failed', 'drone_returned', 'in_transit'])) {
            $finalStatus = match($status) {
                'delivered' => 'delivered',
                'canceled' => 'canceled',
                'failed' => 'failed',
                'drone_returned' => 'failed',
                default => $finalStatus
            };
            
            // Directly update the assignment to ensure it's set to the correct status
            $updated = DeliveryAssignment::where('delivery_id', $delivery->id)
                ->where('stage', $stageNumber)
                ->update(['status' => $finalStatus]);
        } else {
            $updated = $delivery->assignments()
                ->where('delivery_id', $delivery->id)
                ->where('stage', $stageNumber)
                ->update(['status' => $finalStatus]);

            Log::debug('Assignment status updated', [
                'delivery_id' => $delivery->id,
                'stage' => $stageNumber,
                'status' => $finalStatus,
                'rows_updated' => $updated
            ]);
        }

        Log::debug('Final assignment status decision', [
            'stage_status' => $status,
            'delivery_status' => $updateData['status'],
            'final_status' => $finalStatus,
            'stage_number' => $stageNumber,
            'stage_type' => $stageType,
            'is_drone_status' => in_array($status, ['drone_launched', 'drone_in_route', 'drone_arrived'])
        ]);

        Log::debug('Assignment update result', [
            'rows_updated' => $updated,
            'assignment_status' => $assignmentStatus,
            'final_status' => $finalStatus,
            'query' => $delivery->assignments()
                ->where('delivery_id', $delivery->id)
                ->where('stage', $stageNumber)
                ->toSql(),
            'force_delivered' => ($status === 'delivered'),
            'assignment_exists' => DeliveryAssignment::where('delivery_id', $delivery->id)
                ->where('stage', $stageNumber)
                ->exists()
        ]);

        if ($updated === 0) {
            Log::error('Failed to update assignment status', [
                'delivery_id' => $delivery->id,
                'stage' => $stageNumber,
                'expected_status' => $finalStatus,
                'current_assignment' => DeliveryAssignment::where('delivery_id', $delivery->id)
                    ->where('stage', $stageNumber)
                    ->first()
            ]);
        }

        Log::info('Delivery status update', [
            'delivery_id' => $delivery->id,
            'status' => $status,
            'stage_type' => $stageType,
            'all_stages_complete' => $allStagesComplete
        ]);

        $this->createNotification(
            $delivery->customer_id,
            'delivery_updated',
            [
                'delivery_id' => $delivery->id,
                'status' => $status,
                'message' => "Stage $stageType updated to: $status"
            ]
        );

        return $delivery;
    }

    /**
     * Create a notification for status changes
     */
    private function createNotification(string $userId, string $type, array $data): void
    {
        Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'data' => $data
        ]);
    }
}
