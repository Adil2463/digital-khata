<?php

declare(strict_types=1);

namespace App\Modules\Shared\Realtime\Support;

use App\Modules\Shared\Realtime\Contracts\RealtimeDispatcher;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

final readonly class LaravelRealtimeDispatcher implements RealtimeDispatcher
{
    public function __construct(
        private BroadcastManager $broadcastManager,
    ) {}

    public function dispatch(ShouldBroadcast $shouldBroadcast): void
    {
        $pendingBroadcast = $this->broadcastManager->event($shouldBroadcast);

        unset($pendingBroadcast);
    }

    public function dispatchToOthers(ShouldBroadcast $shouldBroadcast, ?string $socketId = null): void
    {
        if ($socketId !== null && method_exists($shouldBroadcast, 'dontBroadcastToSocket')) {
            $shouldBroadcast->dontBroadcastToSocket($socketId);
        }

        $pendingBroadcast = $this->broadcastManager->event($shouldBroadcast);

        if ($socketId !== null && ! method_exists($shouldBroadcast, 'dontBroadcastToSocket')) {
            $pendingBroadcast->toOthers();
        }

        unset($pendingBroadcast);
    }
}
