<?php

declare(strict_types=1);

namespace App\Modules\Shared\Providers;

use App\Models\User;
use App\Modules\Shared\Realtime\Contracts\RealtimeDispatcher;
use App\Modules\Shared\Realtime\Data\PresenceMemberData;
use App\Modules\Shared\Realtime\Support\LaravelRealtimeDispatcher;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;
use Override;

final class ModuleServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton(RealtimeDispatcher::class, LaravelRealtimeDispatcher::class);
    }

    public function boot(): void
    {
        Broadcast::resolved(static function (BroadcastManager $broadcastManager): void {
            $broadcaster = $broadcastManager->connection();

            if (! $broadcaster instanceof Broadcaster) {
                return;
            }

            $broadcaster->resolveAuthenticatedUserUsing(static function (Request $request): ?array {
                $user = $request->user();

                if (! $user instanceof User) {
                    return null;
                }

                return PresenceMemberData::fromUser($user)->toArray();
            });
        });
    }
}
