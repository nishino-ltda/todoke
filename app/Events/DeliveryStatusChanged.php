<?php

namespace App\Events;

use App\Models\Delivery;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Delivery $delivery)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("deliveries.{$this->delivery->id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'delivery_id' => $this->delivery->id,
            'status' => $this->delivery->status,
            'message' => "Delivery #{$this->delivery->id} status updated to {$this->delivery->status}",
        ];
    }
}
