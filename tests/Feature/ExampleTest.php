<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('returns a successful response', function (): void {
    $testResponse = $this->get('/');

    $testResponse->assertStatus(200);
    $testResponse->assertInertia(fn (Assert $assert): Assert => $assert->component('modules/marketing/pages/Home'));
});
