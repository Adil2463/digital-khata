<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Requests\Concerns;

use Illuminate\Support\Str;

trait PreparesAccountInput
{
    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['email' => 'login'];
    }

    /**
     * Login identifiers are compared case-insensitively, so they are stored lower-case.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => mb_trim($this->string('name')->toString()),
            'email' => Str::lower(mb_trim($this->string('email')->toString())),
        ]);
    }
}
