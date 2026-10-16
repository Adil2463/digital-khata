<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Requests;

use App\Models\User;
use App\Modules\Settings\Data\ProfileUpdateData;
use App\Modules\Shared\Http\Requests\DataFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * @extends DataFormRequest<ProfileUpdateData>
 */
final class ProfileUpdateRequest extends DataFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()?->id),
            ],
        ];
    }

    protected function dataClass(): string
    {
        return ProfileUpdateData::class;
    }
}
