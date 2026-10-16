<?php

declare(strict_types=1);

namespace App\Modules\Users\Data;

use App\Models\User;
use App\Modules\Shared\Data\PaginationData;
use App\Modules\Shared\Data\UserViewData;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class UsersIndexPageData extends Data
{
    /**
     * @param  list<UserViewData>  $items
     */
    public function __construct(
        public array $items,
        public PaginationData $pagination,
    ) {}

    /**
     * @param  LengthAwarePaginator<int, User>  $lengthAwarePaginator
     */
    public static function fromPaginator(LengthAwarePaginator $lengthAwarePaginator): self
    {
        /** @var list<User> $users */
        $users = $lengthAwarePaginator->items();

        return new self(
            items: array_map(
                static fn (User $user): UserViewData => $user->toViewData(),
                $users,
            ),
            pagination: PaginationData::fromPaginator($lengthAwarePaginator),
        );
    }
}
