<?php

declare(strict_types=1);

namespace App\Modules\Users\Data;

use App\Enums\UserRole;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class CreateUserData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public UserRole $role,
        public string $password,
    ) {}
}
