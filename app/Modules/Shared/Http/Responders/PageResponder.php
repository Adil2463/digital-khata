<?php

declare(strict_types=1);

namespace App\Modules\Shared\Http\Responders;

use Illuminate\Contracts\Support\Arrayable;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\Data;

final class PageResponder
{
    /**
     * @param  Arrayable<string, mixed>|array<string, mixed>|Data|null  $payload
     */
    public static function render(string $component, Arrayable|array|Data|null $payload = null): Response
    {
        return Inertia::render($component, self::normalizePayload($payload));
    }

    /**
     * @param  Arrayable<string, mixed>|array<string, mixed>|Data|null  $payload
     * @return array<string, mixed>
     */
    private static function normalizePayload(Arrayable|array|Data|null $payload): array
    {
        if ($payload instanceof Data) {
            /** @var array<string, mixed> $data */
            $data = $payload->toArray();

            return $data;
        }

        if ($payload instanceof Arrayable) {
            /** @var array<string, mixed> $data */
            $data = $payload->toArray();

            return $data;
        }

        return $payload ?? [];
    }
}
