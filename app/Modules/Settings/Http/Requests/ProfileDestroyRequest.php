<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Requests;

use App\Modules\Settings\Data\ProfileDestroyData;
use App\Modules\Shared\Http\Requests\DataFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * @extends DataFormRequest<ProfileDestroyData>
 */
final class ProfileDestroyRequest extends DataFormRequest
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
            'password' => ['required', 'current_password'],
        ];
    }

    protected function dataClass(): string
    {
        return ProfileDestroyData::class;
    }
}
