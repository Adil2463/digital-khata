<?php

declare(strict_types=1);

namespace App\Modules\Shared\Realtime\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

abstract class RealtimeEvent implements ShouldBroadcast
{
    use InteractsWithSockets;
    use SerializesModels;

    public bool $afterCommit = true;

    public string $broadcastQueue = 'realtime';

    /**
     * @return array<string, mixed>
     */
    abstract protected function payload(): array;

    /**
     * @return array<string, mixed>
     */
    final public function broadcastWith(): array
    {
        return $this->payload();
    }

    final public function broadcastQueue(): string
    {
        return $this->broadcastQueue;
    }

    final public function dontBroadcastToSocket(string $socketId): static
    {
        $this->socket = $socketId;

        return $this;
    }
}
