<?php

declare(strict_types=1);

namespace App\Modules\Shared\Data;

use Illuminate\Contracts\Session\Session;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class SharedFlashData extends Data
{
    public function __construct(
        public ?string $message,
        public ?string $error,
        public ?string $status,
    ) {}

    public static function fromSession(Session $session): self
    {
        $message = $session->get('message');
        $error = $session->get('error');
        $status = $session->get('status');

        return new self(
            is_string($message) ? $message : null,
            is_string($error) ? $error : null,
            is_string($status) ? $status : null,
        );
    }
}
