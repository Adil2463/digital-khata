<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Requests;

use App\Modules\Users\Http\Requests\Concerns\AuthorizesRoutedAccount;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UserDeactivateRequest extends FormRequest
{
    use AuthorizesRoutedAccount;

    public function authorize(): Response
    {
        return $this->authorizeRoutedAccount('deactivate');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
