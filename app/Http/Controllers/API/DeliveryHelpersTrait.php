<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait DeliveryHelpersTrait
{
    protected function getUserFromToken(?string $bearerToken, Request $request)
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

    protected function validateDeliveryRequest(Request $request)
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

    protected function prepareDeliveryData(Request $request, $user, float $value, int $estimatedTime): array
    {
        $deliveryData = [
            'customer_id' => $user->id,
            'origin' => [
                'lat' => $request->origin['lat'],
                'lng' => $request->origin['lng'],
                'address' => strip_tags($request->origin['address'])
            ],
            'destination' => [
                'lat' => $request->destination['lat'],
                'lng' => $request->destination['lng'],
                'address' => strip_tags($request->destination['address'])
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
            $motoboyNode = \App\Models\Node::where('type', 'delivery_point')
                ->where('status', 'active')
                ->first();

            $droneNode = \App\Models\Node::where('type', 'distribution_center')
                ->where('status', 'active')
                ->first();

            \Illuminate\Support\Facades\Log::debug('Found nodes for hybrid delivery', [
                'motoboy_node' => $motoboyNode ? $motoboyNode->toArray() : null,
                'drone_node' => $droneNode ? $droneNode->toArray() : null
            ]);
            
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
            
            $deliveryData['logistics_partner_id'] = $deliveryData['stages'][0]['partner_id'];
            
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
            
            \Illuminate\Support\Facades\Log::info('Setting hybrid delivery stages and assignments', [
                'stages' => $deliveryData['stages'],
                'assignments' => $deliveryData['assignments']
            ]);
        } else {
            $deliveryData['stages'] = null;
        }

        return $deliveryData;
    }

    protected function prepareDeliveryResponse($delivery, $user, float $value, int $estimatedTime): array
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

    protected function calculateDeliveryValue(Request $request): float
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

    protected function calculateEstimatedTime(Request $request): int
    {
        return match($request->type) {
            'express' => 30,
            'priority' => 20,
            default => 60
        };
    }
}
