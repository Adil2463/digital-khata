<?php

declare(strict_types=1);

namespace App\Modules\Auth\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[MapInputName(SnakeCaseMapper::class)]
#[TypeScript]
final class ResetPasswordData extends Data
{
    public function __construct(
        public string $token,
        public string $email,
        public string $password,
        public string $passwordConfirmation,
    ) {}
}
