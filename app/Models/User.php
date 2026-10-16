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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @property int|null $shop_id
 * @property CarbonInterface|null $deactivated_at
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

    /**
     * Mirrors the column defaults so sign-in checks never read a missing attribute on a fresh model.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'shop_id' => null,
        'deactivated_at' => null,
    ];

    protected $hidden = ['password', 'remember_token'];

    /**
     * @return BelongsTo<Shop, $this>
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function isOwner(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Only active accounts attached to a shop may sign in or keep an authenticated session.
     */
    public function canSignIn(): bool
    {
        return $this->deactivated_at === null && $this->shop_id !== null;
    }

    public function belongsToShopOf(self $user): bool
    {
        return $this->shop_id !== null && $this->shop_id === $user->shop_id;
    }

    public function receivesBroadcastNotificationsOn(): string
    {
        return ChannelPatternResolver::resolve(SharedRealtimeChannel::UserNotifications->value, ['userId' => $this->id]);
    }

    public function toViewData(): UserViewData
    {
        return UserViewData::fromModel($this);
    }

    /**
     * @return array<string, class-string<UserRole>|string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }
}
