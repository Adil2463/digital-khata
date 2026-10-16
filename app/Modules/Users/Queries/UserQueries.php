<?php

declare(strict_types=1);

namespace App\Modules\Users\Queries;

use App\Models\User;
use App\Modules\Users\Data\UserIndexData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class UserQueries
{
    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(UserIndexData $userIndexData): LengthAwarePaginator
    {
        /** @var Builder<User> $query */
        $query = User::query();

        $this->applySearch($query, $userIndexData->search);

        return $query->orderBy($userIndexData->sortBy->value, $userIndexData->sortDirection->value)
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

        $query->where(function (Builder $builder) use ($searchTerm): void {
            $builder
                ->where('name', 'like', $searchTerm)
                ->orWhere('email', 'like', $searchTerm)
                ->orWhere('role', 'like', $searchTerm);
        });
    }
}
