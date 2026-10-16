<?php

declare(strict_types=1);

namespace App\Modules\Users\Enums;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum UsersRealtimeAction: string
{
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
}
