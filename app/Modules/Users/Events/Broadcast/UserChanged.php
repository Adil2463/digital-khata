<?php

declare(strict_types=1);

namespace App\Modules\Users\Events\Broadcast;

use App\Modules\Shared\Realtime\Events\RealtimeEvent;
use App\Modules\Shared\Realtime\Support\ChannelPatternResolver;
use App\Modules\Users\Data\UserChangedBroadcastData;
use App\Modules\Users\Enums\UsersRealtimeChannel;
use App\Modules\Users\Enums\UsersRealtimeEvent;
use Illuminate\Broadcasting\PrivateChannel;

final class UserChanged extends RealtimeEvent
{
    public function __construct(
        private readonly UserChangedBroadcastData $userChangedBroadcastData,
    ) {}

    public function broadcastAs(): string
    {
        return UsersRealtimeEvent::UserChanged->value;
    }

    /**
     * @return PrivateChannel[]
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(ChannelPatternResolver::resolve(
                UsersRealtimeChannel::User->value,
                ['userId' => $this->userChangedBroadcastData->targetUserId],
            )),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(): array
    {
        return $this->userChangedBroadcastData->transform();
    }
}
