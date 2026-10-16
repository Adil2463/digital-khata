<?php

declare(strict_types=1);

namespace App\Modules\Shared\Http\Requests;

use App\Modules\Shared\Data\PaginationQueryData;
use Illuminate\Contracts\Validation\ValidationRule;
use Override;

/** @extends DataQueryRequest<PaginationQueryData> */
final class PaginationQueryRequest extends DataQueryRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<ValidationRule|string>> */
    public function rules(): array
    {
        return $this->paginationRules();
    }

    protected function dataClass(): string
    {
        return PaginationQueryData::class;
    }

    /** @return array<string, mixed> */
    #[Override]
    protected function dtoDefaults(): array
    {
        return new PaginationQueryData()->toArray();
    }
}
