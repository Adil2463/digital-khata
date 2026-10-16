<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Shared\Enums\SharedRealtimeChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(SharedRealtimeChannel::UserNotifications->value, static fn (User $user, int $userId): bool => $user->id === $userId);
