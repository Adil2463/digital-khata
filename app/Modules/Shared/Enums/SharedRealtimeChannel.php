<?php

declare(strict_types=1);

namespace App\Modules\Shared\Enums;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum SharedRealtimeChannel: string
{
    case UserNotifications = 'users.{userId}.notifications';
}
