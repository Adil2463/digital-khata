<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use App\Modules\Auth\Data\ConfirmPasswordData;
use App\Modules\Shared\Http\Requests\DataFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * @extends DataFormRequest<ConfirmPasswordData>
 */
final class ConfirmPasswordRequest extends DataFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
        ];
    }

    protected function dataClass(): string
    {
        return ConfirmPasswordData::class;
    }
}
