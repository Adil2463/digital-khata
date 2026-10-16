<?php

declare(strict_types=1);

namespace App\Modules\Shared\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class PaginationQueryData extends Data
{
    public function __construct(public int $page = 1, public int $perPage = 15) {}
}
