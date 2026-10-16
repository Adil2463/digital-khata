<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use App\Modules\Auth\Data\PasswordResetLinkData;
use App\Modules\Shared\Http\Requests\DataFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * @extends DataFormRequest<PasswordResetLinkData>
 */
final class PasswordResetLinkRequest extends DataFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    protected function dataClass(): string
    {
        return PasswordResetLinkData::class;
    }
}
