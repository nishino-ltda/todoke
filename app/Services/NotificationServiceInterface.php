<?php

namespace App\Services;

interface NotificationServiceInterface
{
    public function createDeliveryNotification(string $userId, string $type, array $data): void;
}
