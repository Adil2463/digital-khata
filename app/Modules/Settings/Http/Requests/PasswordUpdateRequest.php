<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Requests;

use App\Modules\Settings\Data\PasswordUpdateData;
use App\Modules\Shared\Http\Requests\DataFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Password;
use Override;

/**
 * @extends DataFormRequest<PasswordUpdateData>
 */
final class PasswordUpdateRequest extends DataFormRequest
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
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];
    }

    protected function dataClass(): string
    {
        return PasswordUpdateData::class;
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function dtoPayload(): array
    {
        return array_merge($this->validated(), [
            'password_confirmation' => $this->string('password_confirmation')->toString(),
        ]);
    }
}
