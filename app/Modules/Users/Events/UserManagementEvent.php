<?php

declare(strict_types=1);

namespace App\Modules\Users\Events;

use App\Models\User;
use App\Modules\Shared\Mutations\MutationContext;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when user management actions occur.
 * Listeners can handle side effects like audit logging, notifications, etc.
 */
final readonly class UserManagementEvent implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  MutationContext<User, User|null>  $context
     */
    public function __construct(public MutationContext $context) {}
}
