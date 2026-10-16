<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support\ModuleGeneration;

final class ScaffoldType
{
    public const string PAGE = 'page';

    public const string CRUD = 'crud';

    public const string API = 'api';

    public const string CRUD_API = 'crud-api';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return [self::PAGE, self::CRUD, self::API, self::CRUD_API];
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }

    public static function includesCrud(string $value): bool
    {
        return in_array($value, [self::CRUD, self::CRUD_API], true);
    }

    public static function includesApi(string $value): bool
    {
        return in_array($value, [self::API, self::CRUD_API], true);
    }
}
