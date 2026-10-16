<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support\ModuleGeneration;

/**
 * Sorts the `use` block of a rendered PHP stub.
 *
 * A stub cannot hardcode a correct position for a shared import: whether
 * `App\Modules\Shared\...` sorts before or after `App\Modules\<Module>\...` depends on
 * the module name the generator was given. Sorting after rendering makes the output
 * ordered for every module name instead of only the ones the stub was written against,
 * so generated code satisfies Pint's `ordered_imports` without a formatting pass.
 */
final class PhpUseStatementSorter
{
    /**
     * Mirrors PHP-CS-Fixer's alphabetical import order: namespace separators are
     * compared as spaces so a shorter namespace sorts before a deeper one, and the
     * comparison is case-insensitive.
     */
    public static function compare(string $first, string $second): int
    {
        return strcasecmp(str_replace('\\', ' ', $first), str_replace('\\', ' ', $second));
    }

    public static function sort(string $contents): string
    {
        $lines = explode("\n", $contents);
        $start = null;
        $end = null;

        // Only the first uninterrupted run of top-level imports is reordered. Trait
        // imports and closure `use` clauses are indented, so they never match here.
        foreach ($lines as $index => $line) {
            if (preg_match('/^use .+;$/', $line) !== 1) {
                if ($start !== null) {
                    break;
                }

                continue;
            }

            $start ??= $index;
            $end = $index;
        }

        if ($start === null || $end === null) {
            return $contents;
        }

        $length = $end - $start + 1;
        $block = array_slice($lines, $start, $length);
        usort($block, self::compare(...));
        array_splice($lines, $start, $length, $block);

        return implode("\n", $lines);
    }
}
