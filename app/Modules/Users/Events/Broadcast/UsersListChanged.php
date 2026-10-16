<?php

declare(strict_types=1);

namespace App\Modules\Users\Events\Broadcast;

use App\Modules\Shared\Realtime\Events\RealtimeEvent;
use App\Modules\Users\Data\UsersListChangedBroadcastData;
use App\Modules\Users\Enums\UsersRealtimeChannel;
use App\Modules\Users\Enums\UsersRealtimeEvent;
use Illuminate\Broadcasting\PrivateChannel;

final class UsersListChanged extends RealtimeEvent
{
    public function __construct(
        private readonly UsersListChangedBroadcastData $usersListChangedBroadcastData,
    ) {}

    public function broadcastAs(): string
    {
        return UsersRealtimeEvent::ListChanged->value;
    }

    /**
     * @return PrivateChannel[]
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(UsersRealtimeChannel::Index->value),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(): array
    {
        return $this->usersListChangedBroadcastData->transform();
    }
}
