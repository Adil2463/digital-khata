<?php

declare(strict_types=1);

namespace App\Modules\Shared\Http\Responders;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

final class ApiResponder
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public static function payload(array $payload, int $status = 200): JsonResponse
    {
        return response()->json($payload, $status);
    }

    public static function resource(JsonResource $jsonResource, int $status = 200): JsonResponse
    {
        return $jsonResource->response()->setStatusCode($status);
    }

    public static function collection(AnonymousResourceCollection $anonymousResourceCollection): AnonymousResourceCollection
    {
        return $anonymousResourceCollection;
    }

    public static function noContent(): JsonResponse
    {
        return self::payload([], 204);
    }
}
