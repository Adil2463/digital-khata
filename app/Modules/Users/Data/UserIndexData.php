<?php

declare(strict_types=1);

namespace App\Modules\Users\Data;

use App\Modules\Shared\Enums\SortDirection;
use App\Modules\Users\Enums\UserSortBy;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class UserIndexData extends Data
{
    public function __construct(
        public int $page,
        public int $perPage,
        public ?string $search,
        public UserSortBy $sortBy,
        public SortDirection $sortDirection,
    ) {}
}
