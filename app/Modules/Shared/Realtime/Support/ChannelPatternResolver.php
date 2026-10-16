<?php

declare(strict_types=1);

namespace App\Modules\Shared\Realtime\Support;

use BackedEnum;
use InvalidArgumentException;
use Stringable;

final class ChannelPatternResolver
{
    /**
     * @param  array<string, BackedEnum|Stringable|bool|float|int|string|null>  $parameters
     */
    public static function resolve(string $pattern, array $parameters = []): string
    {
        return preg_replace_callback('/\{([^}]+)\}/', static function (array $matches) use ($parameters): string {
            $parameter = $matches[1];

            if (! array_key_exists($parameter, $parameters)) {
                throw new InvalidArgumentException(sprintf('Missing channel parameter "%s".', $parameter));
            }

            $value = $parameters[$parameter];

            return self::normalizeValue($parameter, $value);
        }, $pattern) ?? $pattern;
    }

    /**
     * @param  BackedEnum|Stringable|bool|float|int|string|null  $value
     */
    private static function normalizeValue(string $parameter, mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof Stringable) {
            return $value->__toString();
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value) || is_string($value)) {
            return (string) $value;
        }

        throw new InvalidArgumentException(sprintf('Channel parameter "%s" must be scalar, enum, or stringable.', $parameter));
    }
}
