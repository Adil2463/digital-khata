<?php

declare(strict_types=1);

namespace App\Modules\Auth\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class LoginPageData extends Data
{
    public function __construct(
        public bool $canResetPassword,
        public ?string $status,
    ) {}
}
