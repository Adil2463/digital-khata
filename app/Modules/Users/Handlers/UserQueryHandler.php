<?php

declare(strict_types=1);

namespace App\Modules\Users\Handlers;

use App\Models\User;
use App\Modules\Users\Data\UserIndexData;
use App\Modules\Users\Queries\UserQueries;
use Illuminate\Pagination\LengthAwarePaginator;
use RuntimeException;

final readonly class UserQueryHandler
{
    public function __construct(
        private UserQueries $userQueries,
    ) {}

    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function index(UserIndexData $userIndexData, User $user): LengthAwarePaginator
    {
        if ($user->shop_id === null) {
            throw new RuntimeException('Only accounts that belong to a shop can list its users.');
        }

        return $this->userQueries->paginate($userIndexData, $user->shop_id);
    }
}
