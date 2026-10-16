<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support\ModuleGeneration;

use InvalidArgumentException;

final readonly class ModuleName
{
    /**
     * @param  list<string>  $namespaceSegments
     */
    private function __construct(
        public array $namespaceSegments,
        public string $namespace,
        public string $path,
        public string $frontendKebab,
    ) {}

    public static function fromString(string $value): self
    {
        $trimmed = mb_trim($value);

        if ($trimmed === '') {
            throw new InvalidArgumentException('The module name cannot be empty.');
        }

        $rawSegments = preg_split('/[\\\\\/]+/', $trimmed);
        $rawSegments = is_array($rawSegments) ? $rawSegments : [];

        $namespaceSegments = [];
        $frontendParts = [];

        foreach ($rawSegments as $rawSegment) {
            $parts = self::splitParts($rawSegment);

            if ($parts === []) {
                continue;
            }

            $segment = self::toPascal($parts);
            if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $segment) !== 1) {
                throw new InvalidArgumentException('Module namespace segments must start with a letter.');
            }

            $namespaceSegments[] = $segment;

            foreach ($parts as $part) {
                $frontendParts[] = mb_strtolower($part);
            }
        }

        if ($namespaceSegments === [] || $frontendParts === []) {
            throw new InvalidArgumentException('Could not derive a valid module name. Use letters and numbers only.');
        }

        return new self(
            namespaceSegments: $namespaceSegments,
            namespace: implode('\\', $namespaceSegments),
            path: implode('/', $namespaceSegments),
            frontendKebab: implode('-', $frontendParts),
        );
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
            $trimmedPart = mb_trim($rawPart);

            if ($trimmedPart !== '') {
                $parts[] = $trimmedPart;
            }
        }

        return $parts;
    }

    /**
     * @param  list<string>  $parts
     */
    private static function toPascal(array $parts): string
    {
        $normalized = array_map(
            static fn (string $part): string => ucfirst(mb_strtolower($part)),
            $parts,
        );

        return implode('', $normalized);
    }
}
