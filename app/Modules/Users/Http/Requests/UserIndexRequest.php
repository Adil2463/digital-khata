<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Requests;

use App\Models\User;
use App\Modules\Shared\Enums\SortDirection;
use App\Modules\Shared\Http\Requests\DataQueryRequest;
use App\Modules\Users\Data\UserIndexData;
use App\Modules\Users\Enums\UserSortBy;
use Illuminate\Contracts\Validation\ValidationRule;
use Override;

/**
 * @extends DataQueryRequest<UserIndexData>
 */
final class UserIndexRequest extends DataQueryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', User::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->paginationRules(maxPerPage: 100),
            'search' => $this->searchRules(maxLength: 100),
            'sortBy' => $this->enumRules(UserSortBy::class),
            'sortDirection' => $this->enumRules(SortDirection::class),
        ];
    }

    protected function dataClass(): string
    {
        return UserIndexData::class;
    }

    /**
     * @return array<string, int|SortDirection|UserSortBy|string|null>
     */
    #[Override]
    protected function dtoDefaults(): array
    {
        return [
            'page' => 1,
            'perPage' => 10,
            'search' => null,
            'sortBy' => UserSortBy::CreatedAt,
            'sortDirection' => SortDirection::Desc,
        ];
    }
}
