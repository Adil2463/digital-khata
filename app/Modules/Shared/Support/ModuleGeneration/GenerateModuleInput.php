<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support\ModuleGeneration;

use App\Enums\UserRole;
use InvalidArgumentException;
use PhpToken;

final readonly class GenerateModuleInput
{
    /**
     * @param  list<string>  $allowedRoles
     * @param  list<string>  $middleware
     * @param  list<string>  $apiMiddleware
     */
    private function __construct(
        public ModuleName $moduleName,
        public string $pagePascalName,
        public string $pageKebabName,
        public string $scaffoldType,
        public bool $generateCrud,
        public bool $generateApi,
        public bool $extend,
        public bool $generatePage,
        public string $routeProfile,
        public string $routePrefix,
        public string $routeNamePrefix,
        public array $allowedRoles,
        public array $middleware,
        public string $apiRouteProfile,
        public string $apiRoutePrefix,
        public string $apiRouteNamePrefix,
        public array $apiMiddleware,
        public bool $generateModel,
        public bool $generateApiResource,
        public bool $generateApiFeatureTest,
        public bool $force,
        public bool $dryRun,
        public string $basePath,
        public ?CrudResourceManifest $crudResourceManifest,
    ) {}

    /**
     * @param  list<string>  $allowedRoles
     * @param  list<string>  $middleware
     * @param  list<string>  $apiMiddleware
     */
    public static function fromValues(
        ModuleName $moduleName,
        string $pageName,
        string $scaffoldType,
        bool $generateCrud,
        bool $generateApi,
        bool $extend,
        bool $generatePage,
        string $routeProfile,
        string $routePrefix,
        string $routeNamePrefix,
        array $allowedRoles,
        array $middleware,
        string $apiRouteProfile,
        string $apiRoutePrefix,
        string $apiRouteNamePrefix,
        array $apiMiddleware,
        bool $generateModel,
        bool $generateApiResource,
        bool $generateApiFeatureTest,
        bool $force,
        bool $dryRun,
        string $basePath,
        ?CrudResourceManifest $crudResourceManifest = null,
    ): self {
        $pagePascalName = self::toPascalCase($pageName);
        $pageKebabName = self::toKebabCase($pageName);

        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $pagePascalName) !== 1 || $pageKebabName === '') {
            throw new InvalidArgumentException('Could not derive a valid page name from the provided value.');
        }

        if ($generateCrud || $generateApi) {
            $modelName = implode('', $moduleName->namespaceSegments);
            $modelToken = PhpToken::tokenize('<?php '.$modelName)[1] ?? null;

            if ($modelToken?->id !== T_STRING || in_array(mb_strtolower($modelName), ['bool', 'int', 'float', 'string', 'object', 'mixed', 'never', 'void', 'iterable', 'null', 'true', 'false', 'self', 'parent', 'enum'], true)) {
                throw new InvalidArgumentException('The derived model name is reserved by PHP: '.$modelName);
            }
        }

        $normalizedRoutePrefix = mb_trim($routePrefix, " \t\n\r\0\x0B/");
        $normalizedRouteNamePrefix = mb_trim($routeNamePrefix, " \t\n\r\0\x0B.");
        $normalizedApiRoutePrefix = mb_trim($apiRoutePrefix, " \t\n\r\0\x0B/");
        $normalizedApiRouteNamePrefix = mb_trim($apiRouteNamePrefix, " \t\n\r\0\x0B.");
        $normalizedAllowedRoles = array_values(array_unique(self::normalizeRoleValues($allowedRoles)));

        if (! ScaffoldType::isValid($scaffoldType)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid scaffold type "%s". Allowed values: %s',
                    $scaffoldType,
                    implode(', ', ScaffoldType::values()),
                ),
            );
        }

        if ($generateCrud !== ScaffoldType::includesCrud($scaffoldType)) {
            throw new InvalidArgumentException('CRUD generation flag must match the selected scaffold type.');
        }

        if ($generateApi !== ScaffoldType::includesApi($scaffoldType)) {
            throw new InvalidArgumentException('API generation flag must match the selected scaffold type.');
        }

        if ($generateCrud && $normalizedRouteNamePrefix === '') {
            throw new InvalidArgumentException('Route name prefix cannot be empty.');
        }

        if ($generateApi && $normalizedApiRouteNamePrefix === '') {
            throw new InvalidArgumentException('API route name prefix cannot be empty.');
        }

        if ($extend && ! $generatePage && ! $generateCrud && ! $generateApi) {
            throw new InvalidArgumentException('The --extend mode requires at least one scaffold target.');
        }

        if ($generatePage && ! $generateCrud && $scaffoldType !== ScaffoldType::PAGE) {
            throw new InvalidArgumentException('Frontend page generation requires CRUD or page scaffolding.');
        }

        if (! $generatePage && ! $generateCrud && ! $generateApi) {
            throw new InvalidArgumentException('At least one scaffold target must be enabled.');
        }

        if (! RouteProfile::isValid($routeProfile)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid route profile "%s". Allowed values: %s',
                    $routeProfile,
                    implode(', ', RouteProfile::values()),
                ),
            );
        }

        if (! ApiRouteProfile::isValid($apiRouteProfile)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid API route profile "%s". Allowed values: %s',
                    $apiRouteProfile,
                    implode(', ', ApiRouteProfile::values()),
                ),
            );
        }

        $validRoleValues = array_map(
            static fn (UserRole $userRole): string => $userRole->value,
            UserRole::cases(),
        );

        foreach ($normalizedAllowedRoles as $normalizedAllowedRole) {
            if (! in_array($normalizedAllowedRole, $validRoleValues, true)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Invalid role "%s". Allowed roles: %s',
                        $normalizedAllowedRole,
                        implode(', ', $validRoleValues),
                    ),
                );
            }
        }

        $normalizedBasePath = mb_rtrim(mb_trim($basePath), '\\/');

        if ($normalizedBasePath === '') {
            throw new InvalidArgumentException('Base path cannot be empty.');
        }

        return new self(
            moduleName: $moduleName,
            pagePascalName: $pagePascalName,
            pageKebabName: $pageKebabName,
            scaffoldType: $scaffoldType,
            generateCrud: $generateCrud,
            generateApi: $generateApi,
            extend: $extend,
            generatePage: $generatePage,
            routeProfile: $routeProfile,
            routePrefix: $normalizedRoutePrefix,
            routeNamePrefix: $normalizedRouteNamePrefix,
            allowedRoles: $normalizedAllowedRoles,
            middleware: array_values(array_unique(self::normalizeMiddleware($middleware))),
            apiRouteProfile: $apiRouteProfile,
            apiRoutePrefix: $normalizedApiRoutePrefix,
            apiRouteNamePrefix: $normalizedApiRouteNamePrefix,
            apiMiddleware: array_values(array_unique(self::normalizeMiddleware($apiMiddleware))),
            generateModel: $generateModel,
            generateApiResource: $generateApiResource,
            generateApiFeatureTest: $generateApiFeatureTest,
            force: $force,
            dryRun: $dryRun,
            basePath: $normalizedBasePath,
            crudResourceManifest: $crudResourceManifest,
        );
    }

    /**
     * @param  array<int|string, mixed>  $roles
     * @return list<string>
     */
    private static function normalizeRoleValues(array $roles): array
    {
        $normalized = [];

        foreach ($roles as $role) {
            if (! is_string($role)) {
                continue;
            }

            $value = mb_strtolower(mb_trim($role));

            if ($value !== '') {
                $normalized[] = $value;
            }
        }

        return $normalized;
    }

    /**
     * @param  array<int|string, mixed>  $middleware
     * @return list<string>
     */
    private static function normalizeMiddleware(array $middleware): array
    {
        $normalized = [];

        foreach ($middleware as $middlewareItem) {
            if (! is_string($middlewareItem)) {
                continue;
            }

            $value = mb_trim($middlewareItem);

            if ($value !== '') {
                $normalized[] = $value;
            }
        }

        return $normalized;
    }

    private static function toPascalCase(string $value): string
    {
        $parts = self::splitParts($value);

        if ($parts === []) {
            return '';
        }

        return implode('', array_map(
            static fn (string $part): string => ucfirst(mb_strtolower($part)),
            $parts,
        ));
    }

    private static function toKebabCase(string $value): string
    {
        $parts = self::splitParts($value);

        if ($parts === []) {
            return '';
        }

        return implode('-', array_map(
            mb_strtolower(...),
            $parts,
        ));
    }

    /**
     * @return list<string>
     */
    private static function splitParts(string $value): array
    {
        $spaced = preg_replace('/([a-z0-9])([A-Z])/u', '$1 $2', $value);

        if (! is_string($spaced)) {
            return [];
        }

        $rawParts = preg_split('/[^a-zA-Z0-9]+/u', $spaced);
        $rawParts = is_array($rawParts) ? $rawParts : [];

        $parts = [];

        foreach ($rawParts as $rawPart) {
            $trimmed = mb_trim($rawPart);

            if ($trimmed !== '') {
                $parts[] = $trimmed;
            }
        }

        return $parts;
    }
}
