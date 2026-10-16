<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // A public directory without a `hot` file puts `@vite` and `@fonts` in production
    // mode, reading these fixture manifests the way a real build is read.
    $publicPath = $this->temporaryDirectoryPath('root-view-public');
    $this->ensureDirectory($publicPath.'/build/assets');

    file_put_contents($publicPath.'/build/assets/fonts.css', implode("\n", [
        '@font-face { font-family: "Instrument Sans"; font-weight: 400 700; src: url("/build/assets/instrument-sans.woff2") format("woff2"); }',
        ':root { --font-instrument-sans: "Instrument Sans", "Instrument Sans fallback", ui-sans-serif, sans-serif; }',
    ]));

    file_put_contents($publicPath.'/build/manifest.json', json_encode([
        'resources/css/app.css' => ['file' => 'assets/app.css', 'src' => 'resources/css/app.css', 'isEntry' => true],
        'resources/js/app.ts' => ['file' => 'assets/app.js', 'src' => 'resources/js/app.ts', 'isEntry' => true],
        'resources/js/modules/auth/pages/Login.vue' => ['file' => 'assets/Login.js', 'src' => 'resources/js/modules/auth/pages/Login.vue', 'isDynamicEntry' => true],
    ], JSON_THROW_ON_ERROR));

    file_put_contents($publicPath.'/build/fonts-manifest.json', json_encode([
        'version' => 1,
        'style' => [
            'file' => 'assets/fonts.css',
            'familyStyles' => [
                'instrument-sans' => '@font-face { font-family: "Instrument Sans"; font-weight: 400 700; src: url("/build/assets/instrument-sans.woff2") format("woff2"); }',
            ],
            'variables' => [
                'instrument-sans' => '--font-instrument-sans: "Instrument Sans", "Instrument Sans fallback", ui-sans-serif, sans-serif;',
            ],
        ],
        'preloads' => [
            ['alias' => 'instrument-sans', 'family' => 'Instrument Sans', 'weight' => '400 700', 'style' => 'normal', 'file' => 'assets/instrument-sans.woff2', 'as' => 'font', 'type' => 'font/woff2', 'crossorigin' => 'anonymous'],
        ],
        'families' => [
            'instrument-sans' => ['family' => 'Instrument Sans', 'variable' => '--font-instrument-sans', 'variants' => []],
        ],
    ], JSON_THROW_ON_ERROR));

    $this->app->usePublicPath($publicPath);
    $this->withVite();
});

test('the root view preloads self-hosted fonts and inlines their faces under the request nonce', function (): void {
    $testResponse = $this->get('/auth/login')->assertOk();

    preg_match("/style-src 'self' 'nonce-([^']+)'/", (string) $testResponse->headers->get('Content-Security-Policy'), $matches);
    $nonce = $matches[1] ?? '';

    expect($nonce)->not->toBe('');
    $testResponse
        ->assertSee(sprintf('<link rel="preload" as="font" href="%s" type="font/woff2" crossorigin="anonymous" nonce="%s" />', asset('build/assets/instrument-sans.woff2'), $nonce), false)
        ->assertSee(sprintf('<style nonce="%s">', $nonce), false)
        ->assertSee('--font-instrument-sans: "Instrument Sans", "Instrument Sans fallback"', false);
    expect((string) $testResponse->headers->get('Link'))->toContain(asset('build/assets/instrument-sans.woff2').'>; rel="preload"; as="font"');
});

test('fonts are served only from the application origin', function (): void {
    $testResponse = $this->get('/auth/login')->assertOk();

    expect((string) $testResponse->headers->get('Content-Security-Policy'))
        ->toContain("font-src 'self';")
        ->not->toContain('fonts.bunny.net');
    $testResponse->assertDontSee('fonts.bunny.net', false);
});
