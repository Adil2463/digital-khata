<?php

declare(strict_types=1);

namespace App\Modules\Shared\Realtime\Contracts;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

interface RealtimeDispatcher
{
    public function dispatch(ShouldBroadcast $shouldBroadcast): void;

    public function dispatchToOthers(ShouldBroadcast $shouldBroadcast, ?string $socketId = null): void;
}
