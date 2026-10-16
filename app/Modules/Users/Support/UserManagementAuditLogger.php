<?php

declare(strict_types=1);

namespace App\Modules\Users\Support;

use App\Models\User;
use App\Modules\Shared\Mutations\MutationContext;
use Illuminate\Support\Facades\Log;

final class UserManagementAuditLogger
{
    /**
     * @param  MutationContext<User, User|null>  $mutationContext
     */
    public static function log(MutationContext $mutationContext): void
    {
        /** @var User $actor */
        $actor = $mutationContext->actor;
        /** @var ?User $target */
        $target = $mutationContext->target;

        Log::channel('audit')->info('audit.user_management', [
            'action' => $mutationContext->action,
            'actor_id' => $actor->id,
            'actor_email' => $actor->email,
            'target_id' => $target?->id,
            'target_email' => $target?->email,
            'target_role' => $target?->role?->value,
            'changes' => $mutationContext->changes,
            'ip_address' => $mutationContext->ipAddress(),
            'user_agent' => $mutationContext->userAgent(),
            'occurred_at' => $mutationContext->occurredAt()->toISOString(),
        ]);
    }
}
