<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support\ModuleGeneration;

final class ApiRouteProfile
{
    public const string PROTECTED = 'protected';

    public const string PUBLIC = 'public';

    public const string CUSTOM = 'custom';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return [self::PROTECTED, self::PUBLIC, self::CUSTOM];
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }
}
