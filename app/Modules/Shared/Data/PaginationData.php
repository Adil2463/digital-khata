<?php

declare(strict_types=1);

namespace App\Modules\Shared\Data;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class PaginationData extends Data
{
    public function __construct(
        public int $current_page,
        public int $last_page,
        public int $per_page,
        public int $total,
    ) {}

    /**
     * @template TItem
     *
     * @param  LengthAwarePaginator<int, TItem>  $lengthAwarePaginator
     */
    public static function fromPaginator(LengthAwarePaginator $lengthAwarePaginator): self
    {
        return new self($lengthAwarePaginator->currentPage(), $lengthAwarePaginator->lastPage(), $lengthAwarePaginator->perPage(), $lengthAwarePaginator->total());
    }
}
