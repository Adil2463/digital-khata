<?php

declare(strict_types=1);

namespace App\Modules\Users\Handlers;

use App\Models\User;
use App\Modules\Users\Data\UserIndexData;
use App\Modules\Users\Queries\UserQueries;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class UserQueryHandler
{
    public function __construct(
        private UserQueries $userQueries,
    ) {}

    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function index(UserIndexData $userIndexData): LengthAwarePaginator
    {
        return $this->userQueries->paginate($userIndexData);
    }
}
