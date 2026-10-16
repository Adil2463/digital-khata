<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\User;
use App\Modules\Shared\Mutations\MutationMetadata;
use App\Modules\Users\Support\UserActionContext;
use Carbon\CarbonImmutable;

trait CreatesUserActionContext
{
    protected function userActionContext(User $user): UserActionContext
    {
        return new UserActionContext(
            actor: $user,
            metadata: new MutationMetadata(
                ipAddress: '127.0.0.1',
                userAgent: 'Pest',
                socketId: '1234.5678',
                occurredAt: CarbonImmutable::parse('2026-03-07T10:15:00+00:00'),
            ),
        );
    }
}
