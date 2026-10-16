<?php

declare(strict_types=1);
use App\Models\User;
use App\Modules\Users\Policies\UserPolicy;
use Illuminate\Contracts\Auth\Access\Gate;

test('module policies are registered by convention', function (): void {
    $policy = app(Gate::class)->getPolicyFor(User::class);

    expect($policy)->toBeInstanceOf(UserPolicy::class);
});
