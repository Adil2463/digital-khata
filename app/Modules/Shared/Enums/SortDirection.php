<?php

declare(strict_types=1);

namespace App\Modules\Shared\Enums;

enum SortDirection: string
{
    case Asc = 'asc';
    case Desc = 'desc';
}
