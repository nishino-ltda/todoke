<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService implements NotificationServiceInterface
{
    public function createDeliveryNotification(string $userId, string $type, array $data): void
    {
        Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'data' => $data
        ]);
    }
}
