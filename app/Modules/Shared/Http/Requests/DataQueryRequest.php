<?php

declare(strict_types=1);

namespace App\Modules\Shared\Http\Requests;

use BackedEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Enum;
use Override;

/**
 * @template TData of \Spatie\LaravelData\Data
 *
 * @extends DataRequest<TData>
 */
abstract class DataQueryRequest extends DataRequest
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function dtoPayload(): array
    {
        return $this->mergeDtoDefaults($this->normalizeDtoPayload($this->validated()));
    }

    /**
     * @return array<string, mixed>
     */
    protected function dtoDefaults(): array
    {
        return [];
    }

    /**
     * @return array{
     *     page: list<ValidationRule|string>,
     *     perPage: list<ValidationRule|string>
     * }
     */
    protected function paginationRules(int $maxPerPage = 100): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'perPage' => ['nullable', 'integer', 'min:1', 'max:'.$maxPerPage],
        ];
    }

    /**
     * @return list<ValidationRule|string>
     */
    protected function searchRules(int $maxLength = 100): array
    {
        return ['nullable', 'string', 'max:'.$maxLength];
    }

    /**
     * @param  class-string<BackedEnum>  $enumClass
     * @return list<ValidationRule|Enum|string>
     */
    protected function enumRules(string $enumClass): array
    {
        return ['nullable', 'string', new Enum($enumClass)];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizeDtoPayload(array $payload): array
    {
        $normalizedPayload = [];

        foreach ($payload as $key => $value) {
            $normalizedPayload[$key] = $this->normalizeDtoValue($value);
        }

        return $normalizedPayload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function mergeDtoDefaults(array $payload): array
    {
        $mergedPayload = $this->dtoDefaults();

        foreach ($payload as $key => $value) {
            if ($value === null && array_key_exists($key, $mergedPayload)) {
                continue;
            }

            $mergedPayload[$key] = $value;
        }

        return $mergedPayload;
    }

    private function normalizeDtoValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $trimmed = mb_trim($value);

            return $trimmed === '' ? null : $trimmed;
        }

        if (! is_array($value)) {
            return $value;
        }

        return array_map($this->normalizeDtoValue(...), $value);
    }
}
