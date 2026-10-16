<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support\ModuleGeneration;

use InvalidArgumentException;

final readonly class CrudResourceManifest
{
    /**
     * @param  list<string>  $allowedRoles
     * @param  list<string>  $middleware
     * @param  non-empty-list<array{
     *     key: string,
     *     label: string,
     *     type: string,
     *     sortable: bool
     * }>  $tableColumns
     * @param  list<array{
     *     key: string,
     *     label: string,
     *     type: string,
     *     class?: string
     * }>  $mobileFields
     * @param  list<array{
     *     name: string,
     *     label: string,
     *     type: string,
     *     required: bool,
     *     placeholder?: string,
     *     autocomplete?: string
     * }>  $formFields
     */
    public function __construct(
        public string $pagePascalName,
        public string $modelClass,
        public string $routeProfile,
        public string $routePrefix,
        public string $routeNamePrefix,
        public array $allowedRoles,
        public array $middleware,
        public CrudApiManifest $api,
        public array $tableColumns,
        public array $mobileFields,
        public array $formFields,
        public bool $realtimeEnabled,
    ) {}

    public static function fromGenerateModuleInput(GenerateModuleInput $generateModuleInput): self
    {
        return new self(
            pagePascalName: $generateModuleInput->pagePascalName,
            modelClass: implode('', $generateModuleInput->moduleName->namespaceSegments),
            routeProfile: $generateModuleInput->routeProfile,
            routePrefix: $generateModuleInput->routePrefix,
            routeNamePrefix: $generateModuleInput->routeNamePrefix,
            allowedRoles: $generateModuleInput->allowedRoles,
            middleware: $generateModuleInput->middleware,
            api: new CrudApiManifest(
                enabled: $generateModuleInput->generateApi,
                routeProfile: $generateModuleInput->apiRouteProfile,
                routePrefix: $generateModuleInput->apiRoutePrefix,
                routeNamePrefix: $generateModuleInput->apiRouteNamePrefix,
                middleware: $generateModuleInput->apiMiddleware,
                generatesResource: $generateModuleInput->generateApiResource,
                generatesFeatureTest: $generateModuleInput->generateApiFeatureTest,
            ),
            tableColumns: [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'sortable' => false],
                ['key' => 'created_at', 'label' => 'Created At', 'type' => 'date', 'sortable' => false],
                ['key' => 'updated_at', 'label' => 'Updated At', 'type' => 'date', 'sortable' => false],
            ],
            mobileFields: [
                ['key' => 'created_at', 'label' => 'Created', 'type' => 'date'],
                ['key' => 'updated_at', 'label' => 'Updated', 'type' => 'date'],
            ],
            formFields: [
                [
                    'name' => 'name',
                    'label' => 'Name',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Enter name',
                ],
            ],
            realtimeEnabled: false,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $route = self::requireNestedArray($data, 'route');
        $api = self::requireNestedArray($data, 'api');
        $table = self::requireNestedArray($data, 'table');
        $form = self::requireNestedArray($data, 'form');
        $realtime = self::requireNestedArray($data, 'realtime');

        return new self(
            pagePascalName: self::requireString($data, 'page'),
            modelClass: self::requireString($data, 'model'),
            routeProfile: self::requireString($route, 'profile'),
            routePrefix: self::requireString($route, 'prefix'),
            routeNamePrefix: self::requireString($route, 'name_prefix'),
            allowedRoles: self::requireStringList($route, 'roles'),
            middleware: self::requireStringList($route, 'middleware'),
            api: CrudApiManifest::fromArray($api),
            tableColumns: self::requireTableColumns($table),
            mobileFields: self::requireMobileFields($table),
            formFields: self::requireFormFields($form),
            realtimeEnabled: self::requireBool($realtime, 'enabled'),
        );
    }

    public static function filePath(string $basePath, ModuleName $moduleName, string $pagePascalName): string
    {
        return sprintf(
            '%s/app/Modules/%s/Manifests/%sResource.php',
            $basePath,
            $moduleName->path,
            $pagePascalName,
        );
    }

    public static function load(string $path): ?self
    {
        if (! is_file($path)) {
            return null;
        }

        $manifest = require $path;

        if (! is_array($manifest)) {
            throw new InvalidArgumentException(sprintf('Resource manifest "%s" must return an array.', $path));
        }

        /** @var array<string, mixed> $manifest */
        return self::fromArray($manifest);
    }

    /**
     * @return array{
     *     page: string,
     *     model: string,
     *     route: array{profile: string, prefix: string, name_prefix: string, roles: list<string>, middleware: list<string>},
     *     api: array{
     *         enabled: bool,
     *         route_profile: string,
     *         route_prefix: string,
     *         route_name_prefix: string,
     *         middleware: list<string>,
     *         generates_resource: bool,
     *         generates_feature_test: bool
     *     },
     *     table: array{
     *         columns: list<array{key: string, label: string, type: string, sortable: bool}>,
     *         mobile_fields: list<array{key: string, label: string, type: string, class?: string}>
     *     },
     *     form: array{
     *         fields: list<array{name: string, label: string, type: string, required: bool, placeholder?: string, autocomplete?: string}>
     *     },
     *     realtime: array{enabled: bool}
     * }
     */
    public function toArray(): array
    {
        return [
            'page' => $this->pagePascalName,
            'model' => $this->modelClass,
            'route' => [
                'profile' => $this->routeProfile,
                'prefix' => $this->routePrefix,
                'name_prefix' => $this->routeNamePrefix,
                'roles' => $this->allowedRoles,
                'middleware' => $this->middleware,
            ],
            'api' => $this->api->toArray(),
            'table' => [
                'columns' => $this->tableColumns,
                'mobile_fields' => $this->mobileFields,
            ],
            'form' => [
                'fields' => $this->formFields,
            ],
            'realtime' => [
                'enabled' => $this->realtimeEnabled,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function requireNestedArray(array $data, string $key): array
    {
        $value = $data[$key] ?? null;

        if (! is_array($value)) {
            throw new InvalidArgumentException(sprintf('Resource manifest key "%s" must be an array.', $key));
        }

        /** @var array<string, mixed> $value */
        return $value;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function requireString(array $data, string $key): string
    {
        $value = $data[$key] ?? null;

        if (! is_string($value) || $value === '') {
            throw new InvalidArgumentException(sprintf('Resource manifest key "%s" must be a non-empty string.', $key));
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function requireBool(array $data, string $key): bool
    {
        $value = $data[$key] ?? null;

        if (! is_bool($value)) {
            throw new InvalidArgumentException(sprintf('Resource manifest key "%s" must be a boolean.', $key));
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
            throw new InvalidArgumentException(sprintf('Resource manifest key "%s" must be a list of strings.', $key));
        }

        $values = [];

        foreach ($value as $item) {
            if (! is_string($item) || $item === '') {
                throw new InvalidArgumentException(sprintf('Resource manifest key "%s" must contain only non-empty strings.', $key));
            }

            $values[] = $item;
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $table
     * @return non-empty-list<array{key: string, label: string, type: string, sortable: bool}>
     */
    private static function requireTableColumns(array $table): array
    {
        $columns = $table['columns'] ?? null;

        if (! is_array($columns) || $columns === []) {
            throw new InvalidArgumentException('Resource manifest table.columns must be a non-empty list.');
        }

        $normalized = [];

        foreach ($columns as $column) {
            if (! is_array($column)) {
                throw new InvalidArgumentException('Each table column must be an array.');
            }

            /** @var array<string, mixed> $column */
            $normalized[] = [
                'key' => self::requireString($column, 'key'),
                'label' => self::requireString($column, 'label'),
                'type' => self::requireString($column, 'type'),
                'sortable' => self::requireBool($column, 'sortable'),
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $table
     * @return list<array{key: string, label: string, type: string, class?: string}>
     */
    private static function requireMobileFields(array $table): array
    {
        $mobileFields = $table['mobile_fields'] ?? null;

        if (! is_array($mobileFields)) {
            throw new InvalidArgumentException('Resource manifest table.mobile_fields must be a list.');
        }

        $normalized = [];

        foreach ($mobileFields as $mobileField) {
            if (! is_array($mobileField)) {
                throw new InvalidArgumentException('Each mobile field must be an array.');
            }

            /** @var array<string, mixed> $mobileField */
            $normalizedField = [
                'key' => self::requireString($mobileField, 'key'),
                'label' => self::requireString($mobileField, 'label'),
                'type' => self::requireString($mobileField, 'type'),
            ];

            $className = $mobileField['class'] ?? null;

            if ($className !== null) {
                if (! is_string($className) || $className === '') {
                    throw new InvalidArgumentException('Mobile field "class" must be a non-empty string when present.');
                }

                $normalizedField['class'] = $className;
            }

            $normalized[] = $normalizedField;
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $form
     * @return list<array{name: string, label: string, type: string, required: bool, placeholder?: string, autocomplete?: string}>
     */
    private static function requireFormFields(array $form): array
    {
        $fields = $form['fields'] ?? null;

        if (! is_array($fields) || $fields === []) {
            throw new InvalidArgumentException('Resource manifest form.fields must be a non-empty list.');
        }

        $normalized = [];

        foreach ($fields as $field) {
            if (! is_array($field)) {
                throw new InvalidArgumentException('Each form field must be an array.');
            }

            /** @var array<string, mixed> $field */
            $normalizedField = [
                'name' => self::requireString($field, 'name'),
                'label' => self::requireString($field, 'label'),
                'type' => self::requireString($field, 'type'),
                'required' => self::requireBool($field, 'required'),
            ];

            $placeholder = $field['placeholder'] ?? null;

            if ($placeholder !== null) {
                if (! is_string($placeholder) || $placeholder === '') {
                    throw new InvalidArgumentException('Form field "placeholder" must be a non-empty string when present.');
                }

                $normalizedField['placeholder'] = $placeholder;
            }

            $autocomplete = $field['autocomplete'] ?? null;

            if ($autocomplete !== null) {
                if (! is_string($autocomplete) || $autocomplete === '') {
                    throw new InvalidArgumentException('Form field "autocomplete" must be a non-empty string when present.');
                }

                $normalizedField['autocomplete'] = $autocomplete;
            }

            $normalized[] = $normalizedField;
        }

        return $normalized;
    }
}
