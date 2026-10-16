<?php

declare(strict_types=1);

namespace App\Modules\Users\Data;

use App\Modules\Users\Enums\UsersRealtimeAction;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class UserManagementNotificationData extends Data
{
    public function __construct(
        public string $title,
        public string $description,
        public UsersRealtimeAction $action,
        public int $actorUserId,
        public string $actorName,
        public ?int $targetUserId,
        public CarbonImmutable $occurredAt,
    ) {}
}
