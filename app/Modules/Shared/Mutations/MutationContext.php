<?php

declare(strict_types=1);

namespace App\Modules\Shared\Mutations;

use Carbon\CarbonImmutable;

/**
 * @template TActor of object
 * @template TTarget of object|null
 */
final readonly class MutationContext
{
    /**
     * @param  TActor  $actor
     * @param  TTarget  $target
     * @param  array<string, mixed>  $changes
     */
    public function __construct(
        public string $action,
        public object $actor,
        public ?object $target,
        public MutationMetadata $metadata,
        public array $changes = [],
    ) {}

    public function ipAddress(): ?string
    {
        return $this->metadata->ipAddress;
    }

    public function userAgent(): ?string
    {
        return $this->metadata->userAgent;
    }

    public function socketId(): ?string
    {
        return $this->metadata->socketId;
    }

    public function occurredAt(): CarbonImmutable
    {
        return $this->metadata->occurredAt;
    }
}
