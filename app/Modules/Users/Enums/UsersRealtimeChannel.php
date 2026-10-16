<?php

declare(strict_types=1);

namespace App\Modules\Users\Enums;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum UsersRealtimeChannel: string
{
    case Index = 'users.index';
    case Presence = 'users.index.presence';
    case User = 'users.{userId}';
}
