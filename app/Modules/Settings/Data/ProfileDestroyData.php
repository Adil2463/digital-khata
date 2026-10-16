<?php

declare(strict_types=1);

namespace App\Modules\Settings\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class ProfileDestroyData extends Data
{
    public function __construct(
        public string $password,
    ) {}
}
