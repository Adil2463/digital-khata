<?php

declare(strict_types=1);

namespace App\Modules\Shared\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class SharedQuoteData extends Data
{
    public function __construct(public string $message, public string $author) {}
}
