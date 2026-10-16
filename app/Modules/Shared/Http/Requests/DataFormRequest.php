<?php

declare(strict_types=1);

namespace App\Modules\Shared\Http\Requests;

use Override;

/**
 * @template TData of \Spatie\LaravelData\Data
 *
 * @extends DataRequest<TData>
 */
abstract class DataFormRequest extends DataRequest
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function dtoPayload(): array
    {
        return $this->validated();
    }
}
