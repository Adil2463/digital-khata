<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Shared\Data\PaginationData;
use App\Modules\Shared\Support\ModuleGeneration\PhpUseStatementSorter;

/**
 * @param  list<string>  $expected
 */
test('orders imports the way pint does regardless of the module namespace', function (string $module, array $expected): void {
    $contents = <<<PHP
    <?php

    namespace App\\Modules\\{$module}\\Http\\Controllers;

    use App\\Http\\Controllers\\Controller;
    use App\\Modules\\Shared\\Http\\Requests\\PaginationQueryRequest;
    use App\\Models\\{$module};
    use App\\Modules\\{$module}\\Queries\\{$module}Queries;

    final class IndexController {}
    PHP;

    $imports = array_values(array_filter(
        explode("\n", PhpUseStatementSorter::sort($contents)),
        static fn (string $line): bool => str_starts_with($line, 'use '),
    ));

    expect($imports)->toBe($expected);
})->with([
    // The module namespace sorts before `Shared` here and after it below, which is why
    // the stubs cannot hardcode a position for the shared import.
    ['Alphaprobe', [
        'use App\Http\Controllers\Controller;',
        'use App\Models\Alphaprobe;',
        'use App\Modules\Alphaprobe\Queries\AlphaprobeQueries;',
        'use App\Modules\Shared\Http\Requests\PaginationQueryRequest;',
    ]],
    ['Zuluprobe', [
        'use App\Http\Controllers\Controller;',
        'use App\Models\Zuluprobe;',
        'use App\Modules\Shared\Http\Requests\PaginationQueryRequest;',
        'use App\Modules\Zuluprobe\Queries\ZuluprobeQueries;',
    ]],
]);

test('a shorter namespace sorts before a deeper one sharing its prefix', function (): void {
    expect(PhpUseStatementSorter::compare(User::class, PaginationData::class))->toBeLessThan(0);
});

test('leaves trait imports and files without a use block untouched', function (): void {
    $contents = <<<'PHP'
    <?php

    final class Example
    {
        use SecondTrait;
        use FirstTrait;
    }
    PHP;

    expect(PhpUseStatementSorter::sort($contents))->toBe($contents);
});
