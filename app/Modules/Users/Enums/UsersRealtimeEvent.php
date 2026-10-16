<?php

declare(strict_types=1);

namespace App\Modules\Users\Enums;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum UsersRealtimeEvent: string
{
    case ListChanged = 'users.list.changed';
    case UserChanged = 'users.user.changed';
}
