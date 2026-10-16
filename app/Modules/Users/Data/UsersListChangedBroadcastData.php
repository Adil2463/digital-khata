<?php

declare(strict_types=1);

namespace App\Modules\Users\Data;

use App\Modules\Users\Enums\UsersRealtimeAction;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class UsersListChangedBroadcastData extends Data
{
    public function __construct(
        public UsersRealtimeAction $action,
        public int $actorUserId,
        public ?int $targetUserId,
        public CarbonImmutable $occurredAt,
    ) {}
}
