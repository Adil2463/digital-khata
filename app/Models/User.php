<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use App\Modules\Shared\Data\UserViewData;
use App\Modules\Shared\Enums\SharedRealtimeChannel;
use App\Modules\Shared\Realtime\Support\ChannelPatternResolver;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Override;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property UserRole $role
 * @property CarbonInterface|null $email_verified_at
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
final class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use MustVerifyEmail;
    use Notifiable;

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

    public function receivesBroadcastNotificationsOn(): string
    {
        return ChannelPatternResolver::resolve(SharedRealtimeChannel::UserNotifications->value, ['userId' => $this->id]);
    }

    public function toViewData(): UserViewData
    {
        return UserViewData::fromModel($this);
    }

    protected static function booted(): void
    {
        self::updating(function (self $user): void {
            if ($user->isDirty('email') && ! $user->isDirty('email_verified_at')) {
                $user->email_verified_at = null;
            }
        });
    }

    /**
     * @return array<string, class-string<UserRole>|string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }
}
