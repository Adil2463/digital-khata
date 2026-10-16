<?php

declare(strict_types=1);

namespace App\Modules\Users\Enums;

enum UserSortBy: string
{
    case Name = 'name';
    case Email = 'email';
    case Role = 'role';
    case CreatedAt = 'created_at';
}
