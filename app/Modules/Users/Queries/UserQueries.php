<?php

declare(strict_types=1);

namespace App\Modules\Users\Queries;

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Users\Data\UserIndexData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class UserQueries
{
    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(UserIndexData $userIndexData, int $shopId): LengthAwarePaginator
    {
        /** @var Builder<User> $builder */
        $builder = User::query()->where('shop_id', $shopId);

        $this->applySearch($builder, $userIndexData->search);

        return $builder->orderBy($userIndexData->sortBy->value, $userIndexData->sortDirection->value)
            ->orderBy('id', $userIndexData->sortDirection->value)
            ->paginate(
                perPage: $userIndexData->perPage,
                page: $userIndexData->page,
            );
    }

    /**
     * @param  Builder<User>  $query
     */
    private function applySearch(Builder $query, ?string $search): void
    {
        if ($search === null || $search === '') {
            return;
        }

        $searchTerm = '%'.$search.'%';
        $matchingRoles = array_values(array_filter(
            UserRole::cases(),
            static fn (UserRole $userRole): bool => str_contains(mb_strtolower($userRole->label()), mb_strtolower($search)),
        ));

        $query->where(function (Builder $builder) use ($searchTerm, $matchingRoles): void {
            $builder
                ->where('name', 'like', $searchTerm)
                ->orWhere('email', 'like', $searchTerm)
                ->orWhereIn('role', $matchingRoles);
        });
    }
}
