<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support\ModuleGeneration;

use InvalidArgumentException;

final readonly class CrudApiManifest
{
    /**
     * @param  list<string>  $middleware
     */
    public function __construct(
        public bool $enabled,
        public string $routeProfile,
        public string $routePrefix,
        public string $routeNamePrefix,
        public array $middleware,
        public bool $generatesResource,
        public bool $generatesFeatureTest,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            enabled: self::requireBool($data, 'enabled'),
            routeProfile: self::requireString($data, 'route_profile'),
            routePrefix: self::requireString($data, 'route_prefix'),
            routeNamePrefix: self::requireString($data, 'route_name_prefix'),
            middleware: self::requireStringList($data, 'middleware'),
            generatesResource: self::requireBool($data, 'generates_resource'),
            generatesFeatureTest: self::requireBool($data, 'generates_feature_test'),
        );
    }

    /**
     * @return array{
     *     enabled: bool,
     *     route_profile: string,
     *     route_prefix: string,
     *     route_name_prefix: string,
     *     middleware: list<string>,
     *     generates_resource: bool,
     *     generates_feature_test: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'route_profile' => $this->routeProfile,
            'route_prefix' => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'middleware' => $this->middleware,
            'generates_resource' => $this->generatesResource,
            'generates_feature_test' => $this->generatesFeatureTest,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function requireBool(array $data, string $key): bool
    {
        $value = $data[$key] ?? null;

        if (! is_bool($value)) {
            throw new InvalidArgumentException(sprintf('API manifest key "%s" must be a boolean.', $key));
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function requireString(array $data, string $key): string
    {
        $value = $data[$key] ?? null;

        if (! is_string($value) || $value === '') {
            throw new InvalidArgumentException(sprintf('API manifest key "%s" must be a non-empty string.', $key));
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    private static function requireStringList(array $data, string $key): array
    {
        $value = $data[$key] ?? null;

        if (! is_array($value)) {
            throw new InvalidArgumentException(sprintf('API manifest key "%s" must be a list of strings.', $key));
        }

        $values = [];

        foreach ($value as $item) {
            if (! is_string($item) || $item === '') {
                throw new InvalidArgumentException(sprintf('API manifest key "%s" must contain only non-empty strings.', $key));
            }

            $values[] = $item;
        }

        return $values;
    }
}
