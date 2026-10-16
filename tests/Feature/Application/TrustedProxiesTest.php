<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    Route::get('/__trusted-proxies-probe', fn (Request $request): array => [
        'secure' => $request->isSecure(),
        'ip' => $request->ip(),
        'root' => $request->root(),
    ]);
});

$forwardedHeaders = [
    'X-Forwarded-For' => '203.0.113.10',
    'X-Forwarded-Proto' => 'https',
    'X-Forwarded-Host' => 'app.example.com',
    'X-Forwarded-Port' => '443',
];

test('forwarded headers are ignored unless a trusted proxy is configured', function () use ($forwardedHeaders): void {
    config(['trustedproxy.proxies' => null]);

    $this->withHeaders($forwardedHeaders)
        ->getJson('/__trusted-proxies-probe')
        ->assertJson(['secure' => false, 'ip' => '127.0.0.1'])
        ->assertJsonMissing(['root' => 'https://app.example.com']);
});

test('forwarded headers from a configured proxy define the scheme, host, and client address', function (string $proxies) use ($forwardedHeaders): void {
    config(['trustedproxy.proxies' => $proxies]);

    $this->withHeaders($forwardedHeaders)
        ->getJson('/__trusted-proxies-probe')
        ->assertExactJson(['secure' => true, 'ip' => '203.0.113.10', 'root' => 'https://app.example.com']);
})->with([
    'wildcard' => '*',
    'address list' => '10.0.0.5, 127.0.0.1',
    'cidr range' => '127.0.0.0/8',
]);

test('forwarded headers from an address outside the configured proxies are ignored', function () use ($forwardedHeaders): void {
    config(['trustedproxy.proxies' => '10.0.0.5']);

    $this->withHeaders($forwardedHeaders)
        ->getJson('/__trusted-proxies-probe')
        ->assertJson(['secure' => false, 'ip' => '127.0.0.1'])
        ->assertJsonMissing(['root' => 'https://app.example.com']);
});
