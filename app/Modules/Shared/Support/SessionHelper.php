<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support;

use Illuminate\Http\Request;

final class SessionHelper
{
    /**
     * Resolve the session flash status as a typed nullable string.
     */
    public static function resolveStatus(Request $request): ?string
    {
        $status = $request->session()->get('status');

        return is_string($status) ? $status : null;
    }
}
