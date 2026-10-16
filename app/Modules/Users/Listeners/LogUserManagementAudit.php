<?php

declare(strict_types=1);

namespace App\Modules\Users\Listeners;

use App\Modules\Users\Events\UserManagementEvent;
use App\Modules\Users\Support\UserManagementAuditLogger;

/**
 * Listener that handles audit logging for user management events.
 */
final class LogUserManagementAudit
{
    /**
     * Handle the event.
     */
    public function handle(UserManagementEvent $userManagementEvent): void
    {
        UserManagementAuditLogger::log($userManagementEvent->context);
    }
}
