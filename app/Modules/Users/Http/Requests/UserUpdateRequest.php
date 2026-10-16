<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Shared\Http\Requests\DataFormRequest;
use App\Modules\Users\Data\UpdateUserData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

/**
 * @extends DataFormRequest<UpdateUserData>
 */
final class UserUpdateRequest extends DataFormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User && ($this->user()?->can('update', $user) ?? false);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($user instanceof User ? $user->id : null),
            ],
            'role' => ['required', 'string', new Enum(UserRole::class)],
            'password' => ['nullable', 'string', Password::defaults()],
        ];
    }

    protected function dataClass(): string
    {
        return UpdateUserData::class;
    }
}
