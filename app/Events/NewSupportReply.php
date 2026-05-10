<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewSupportReply implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $ticketId,
        public string $replyMessage,
        public string $userName
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("support.tickets.{$this->ticketId}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'ticket_id' => $this->ticketId,
            'message' => "New reply on ticket #{$this->ticketId} from {$this->userName}",
            'reply' => $this->replyMessage,
        ];
    }
}
