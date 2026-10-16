<?php

declare(strict_types=1);

namespace App\Modules\Shop\Commands;

use App\Enums\UserRole;
use App\Models\Shop;
use App\Models\User;
use App\Modules\Shared\Auth\CredentialRotator;
use App\Modules\Shop\Data\InstallShopData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final readonly class ShopCommands
{
    public function __construct(
        private CredentialRotator $credentialRotator,
    ) {}

    public static function isInstalled(): bool
    {
        return Shop::query()->exists();
    }

    /**
     * @param  list<User>  $accountsToLock
     */
    public function install(InstallShopData $installShopData, array $accountsToLock): ShopInstallationResult
    {
        $shopInstallationResult = DB::transaction(function () use ($installShopData, $accountsToLock): ShopInstallationResult {
            if (self::isInstalled()) {
                throw new RuntimeException('A shop is already installed.');
            }

            foreach ($accountsToLock as $accountToLock) {
                $this->credentialRotator->lock($accountToLock);
            }

            $shop = Shop::query()->create([
                'name' => $installShopData->shopName,
                'timezone' => $installShopData->timezone,
            ]);

            $owner = new User();
            $owner->forceFill([
                'name' => $installShopData->ownerName,
                'email' => $installShopData->ownerEmail,
                'password' => $installShopData->ownerPassword,
                'role' => UserRole::Admin,
                'shop_id' => $shop->id,
                'email_verified_at' => CarbonImmutable::now(),
            ]);

            if (! $owner->save()) {
                throw new RuntimeException('Owner creation was cancelled.');
            }

            // Single-shop deployment: every account that predates installation belongs to this shop.
            $attachedUserCount = User::query()->whereNull('shop_id')->update(['shop_id' => $shop->id]);

            return new ShopInstallationResult(
                shop: $shop,
                owner: $owner,
                lockedAccountEmails: array_map(static fn (User $user): string => $user->email, $accountsToLock),
                attachedUserCount: $attachedUserCount,
            );
        });

        Log::channel('audit')->info('audit.shop_installed', [
            'shop_id' => $shopInstallationResult->shop->id,
            'owner_id' => $shopInstallationResult->owner->id,
            'owner_email' => $shopInstallationResult->owner->email,
            'locked_account_emails' => $shopInstallationResult->lockedAccountEmails,
            'attached_user_count' => $shopInstallationResult->attachedUserCount,
        ]);

        return $shopInstallationResult;
    }
}
