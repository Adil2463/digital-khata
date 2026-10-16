<?php

declare(strict_types=1);

namespace App\Modules\Users\Notifications;

use App\Models\User;
use App\Modules\Shared\Mutations\MutationContext;
use App\Modules\Users\Data\UserManagementNotificationData;
use App\Modules\Users\Enums\UsersRealtimeAction;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

final class UserManagementBroadcastNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private function __construct(
        private readonly UsersRealtimeAction $usersRealtimeAction,
        private readonly int $actorUserId,
        private readonly string $actorName,
        private readonly ?int $targetUserId,
        private readonly string $targetLabel,
        private readonly CarbonImmutable $occurredAt,
    ) {
        $this->onQueue('realtime');
    }

    /**
     * @param  MutationContext<User, User|null>  $mutationContext
     */
    public static function fromMutationContext(MutationContext $mutationContext): self
    {
        /** @var User $actor */
        $actor = $mutationContext->actor;
        /** @var ?User $target */
        $target = $mutationContext->target;

        return new self(
            usersRealtimeAction: UsersRealtimeAction::from($mutationContext->action),
            actorUserId: $actor->id,
            actorName: $actor->name,
            targetUserId: $target?->id,
            targetLabel: $target instanceof User ? $target->name : 'a user',
            occurredAt: $mutationContext->occurredAt(),
        );
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage(
            UserManagementNotificationData::from([
                'title' => 'User management updated',
                'description' => $this->description(),
                'action' => $this->usersRealtimeAction,
                'actorUserId' => $this->actorUserId,
                'actorName' => $this->actorName,
                'targetUserId' => $this->targetUserId,
                'occurredAt' => $this->occurredAt,
            ])->toArray()
        );
    }

    private function description(): string
    {
        return match ($this->usersRealtimeAction) {
            UsersRealtimeAction::Create => sprintf('%s created %s.', $this->actorName, $this->targetLabel),
            UsersRealtimeAction::Update => sprintf('%s updated %s.', $this->actorName, $this->targetLabel),
            UsersRealtimeAction::Delete => sprintf('%s deleted %s.', $this->actorName, $this->targetLabel),
        };
    }
}
