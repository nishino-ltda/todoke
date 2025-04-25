<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'type' => $notification->type,
                    'delivery_id' => $notification->data['delivery_id'] ?? null,
                    'status' => $notification->data['status'] ?? null,
                    'created_at' => $notification->created_at->toDateTimeString()
                ];
            });

        return response()->json($notifications);
    }
}
