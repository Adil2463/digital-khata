<?php

declare(strict_types=1);

namespace App\Modules\Shop\Http\Requests;

use App\Modules\Shared\Http\Requests\DataFormRequest;
use App\Modules\Shop\Data\ShopSettingsUpdateData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Override;

/**
 * Only the listed fields are accepted. The target shop comes from the signed-in owner and
 * the timezone is not editable in V1, so `shop_id` or `timezone` in the payload is ignored.
 *
 * @extends DataFormRequest<ShopSettingsUpdateData>
 */
final class ShopSettingsUpdateRequest extends DataFormRequest
{
    private const array SINGLE_LINE_FIELDS = ['name', 'address', 'receipt_footer'];

    public function authorize(): bool
    {
        return Gate::allows('manage-shop');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'min:7', 'max:20', 'regex:/^\+?\d+(?:[ -]\d+)*$/'],
            'address' => ['nullable', 'string', 'max:255'],
            'receipt_footer' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public function messages(): array
    {
        return [
            'name.required' => 'Enter the shop name.',
            'phone.regex' => 'Use digits with optional spaces or dashes, for example 0300-1234567 or +92 300 1234567.',
        ];
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public function attributes(): array
    {
        return [
            'name' => 'shop name',
            'receipt_footer' => 'receipt footer',
        ];
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (self::SINGLE_LINE_FIELDS as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $squished = Str::squish($value);
                $normalized[$field] = $squished === '' ? null : $squished;
            }
        }

        $this->merge($normalized);
    }

    protected function dataClass(): string
    {
        return ShopSettingsUpdateData::class;
    }
}
