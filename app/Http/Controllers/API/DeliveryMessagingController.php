<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\DeliveryHelpersTrait;

class DeliveryMessagingController extends Controller
{
    use DeliveryHelpersTrait;

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
}
