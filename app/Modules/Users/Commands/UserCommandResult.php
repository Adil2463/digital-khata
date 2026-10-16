<?php

declare(strict_types=1);

namespace App\Modules\Users\Commands;

use App\Models\User;

final readonly class UserCommandResult
{
    /**
     * @param  array<string, array<string, string>>  $changes
     */
    public function __construct(
        public User $user,
        public array $changes = [],
    ) {}
}
