<?php

declare(strict_types=1);

use Laravel\Wayfinder\TypeScript;

test('wayfinder formats LF CRLF and mixed template lines identically', function (string $separator): void {
    $source = "const routes = {\n    first: {\n        url: '/first',\n    },\n    second: {\n        url: '/second',\n    },\n}\n/**\n * @route '/first'\n */";
    $expected = "const routes = {\n    first: {\n        url: '/first',\n    },\n    second: {\n        url: '/second',\n    },\n}\n\n/**\n* @route '/first'\n*/";

    $input = $separator === 'mixed'
        ? str_replace("first: {\n", "first: {\r\n", $source)
        : str_replace("\n", $separator, $source);

    expect(TypeScript::cleanUp($input))->toBe($expected);
})->with([
    'LF' => "\n",
    'CRLF' => "\r\n",
    'mixed Blade and PHP lines' => 'mixed',
]);
