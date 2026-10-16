<?php

declare(strict_types=1);

namespace App\Modules\Shared\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class SharedAuthData extends Data
{
    public function __construct(public ?UserViewData $user) {}
}
