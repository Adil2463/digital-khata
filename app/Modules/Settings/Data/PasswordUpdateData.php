<?php

declare(strict_types=1);

namespace App\Modules\Settings\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[MapInputName(SnakeCaseMapper::class)]
#[TypeScript]
final class PasswordUpdateData extends Data
{
    public function __construct(
        public string $currentPassword,
        public string $password,
        public string $passwordConfirmation,
    ) {}
}
