<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Shared\Http\Requests\DataFormRequest;
use App\Modules\Users\Data\CreateUserData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

/**
 * @extends DataFormRequest<CreateUserData>
 */
final class UserCreateRequest extends DataFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', Password::defaults()],
            'role' => ['required', 'string', new Enum(UserRole::class)],
        ];
    }

    protected function dataClass(): string
    {
        return CreateUserData::class;
    }
}
