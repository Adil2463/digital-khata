<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('security headers are present on web responses', function (): void {
    $testResponse = $this->get('/');

    $contentSecurityPolicy = (string) $testResponse->headers->get('Content-Security-Policy');

    $this->assertNotSame('', $contentSecurityPolicy);
    expect($contentSecurityPolicy)
        ->toMatch("/script-src 'self' 'nonce-[^']+'/")
        ->toMatch("/style-src 'self' 'nonce-[^']+';/")
        ->toContain("style-src-attr 'unsafe-inline'");
    $testResponse->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $testResponse->assertHeader('X-Content-Type-Options', 'nosniff');
    $testResponse->assertHeader('X-Frame-Options', 'DENY');
    $testResponse->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
});

test('the root view exposes the nonce the content security policy enforces', function (): void {
    $testResponse = $this->get('/');

    preg_match("/script-src 'self' 'nonce-([^']+)'/", (string) $testResponse->headers->get('Content-Security-Policy'), $matches);

    expect($matches[1] ?? null)->toBeString();
    $testResponse->assertSee(sprintf('<meta name="csp-nonce" content="%s">', $matches[1] ?? ''), false);
});

test('each response receives a fresh nonce', function (): void {
    $firstPolicy = (string) $this->get('/')->headers->get('Content-Security-Policy');
    $secondPolicy = (string) $this->get('/')->headers->get('Content-Security-Policy');

    expect($firstPolicy)->not->toBe($secondPolicy);
});
