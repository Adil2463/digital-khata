<?php

declare(strict_types=1);
use App\Enums\Appearance;
use Inertia\Testing\AssertableInertia;

test('default appearance is light when cookie is missing', function (): void {
    $testResponse = $this->get('/');

    $testResponse->assertViewHas('appearance', 'light');
});
test('cookie appearance value is shared with the root view', function (): void {
    $testResponse = $this->withUnencryptedCookies(['appearance' => 'dark'])->get('/');

    $testResponse->assertViewHas('appearance', 'dark');
});
test('unrecognised cookie value falls back to light', function (): void {
    $testResponse = $this->withUnencryptedCookies(['appearance' => 'sepia'])->get('/');

    $testResponse->assertViewHas('appearance', 'light');
});
test('appearance is shared with inertia as well as the root view', function (): void {
    $testResponse = $this->withUnencryptedCookies(['appearance' => 'dark'])->get('/');

    $testResponse->assertInertia(
        fn (AssertableInertia $assertableInertia): AssertableInertia => $assertableInertia->where('appearance', Appearance::Dark->value)
    );
});
test('root element carries the dark class before any script runs', function (): void {
    $testResponse = $this->withUnencryptedCookies(['appearance' => 'dark'])->get('/');

    $testResponse->assertSee('class="dark"', escape: false);
});
