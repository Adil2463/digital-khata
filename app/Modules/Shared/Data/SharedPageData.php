<?php

declare(strict_types=1);

namespace App\Modules\Shared\Data;

use App\Enums\Appearance;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class SharedPageData extends Data
{
    public function __construct(
        public string $name,
        public SharedQuoteData $quote,
        public SharedAuthData $auth,
        public SharedFlashData $flash,
        public string $location,
        public bool $sidebarOpen,
        public Appearance $appearance,
    ) {}
}
