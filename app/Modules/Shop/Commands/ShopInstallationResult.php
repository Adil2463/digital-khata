<?php

declare(strict_types=1);

namespace App\Modules\Shop\Commands;

use App\Models\Shop;
use App\Models\User;

final readonly class ShopInstallationResult
{
    /**
     * @param  list<string>  $lockedAccountEmails
     */
    public function __construct(
        public Shop $shop,
        public User $owner,
        public array $lockedAccountEmails,
        public int $attachedUserCount,
    ) {}
}
