<?php

declare(strict_types=1);

namespace App\Modules\Users\Support;

use App\Models\User;
use App\Modules\Shared\Auth\RequestActor;
use App\Modules\Shared\Mutations\MutationContext;
use App\Modules\Shared\Mutations\MutationMetadata;
use Illuminate\Http\Request;

final readonly class UserActionContext
{
    public function __construct(
        public User $actor,
        public MutationMetadata $metadata,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            actor: RequestActor::from($request),
            metadata: MutationMetadata::fromRequest($request),
        );
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return MutationContext<User, User|null>
     */
    public function mutation(string $action, ?User $user, array $changes = []): MutationContext
    {
        return new MutationContext(
            action: $action,
            actor: clone $this->actor,
            target: $user instanceof User ? clone $user : null,
            metadata: $this->metadata,
            changes: $changes,
        );
    }
}
