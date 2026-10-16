<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use App\Models\User;
use App\Modules\Auth\Data\RegisterUserData;
use App\Modules\Shared\Http\Requests\DataFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Password;

/**
 * @extends DataFormRequest<RegisterUserData>
 */
final class RegisterUserRequest extends DataFormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];
    }

    protected function dataClass(): string
    {
        return RegisterUserData::class;
    }
}
