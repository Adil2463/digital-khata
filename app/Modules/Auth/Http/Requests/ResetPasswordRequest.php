<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use App\Modules\Auth\Data\ResetPasswordData;
use App\Modules\Shared\Http\Requests\DataFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Password;
use Override;

/**
 * @extends DataFormRequest<ResetPasswordData>
 */
final class ResetPasswordRequest extends DataFormRequest
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
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];
    }

    protected function dataClass(): string
    {
        return ResetPasswordData::class;
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
