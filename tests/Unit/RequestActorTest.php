<?php

declare(strict_types=1);
use App\Models\User;
use App\Modules\Shared\Auth\RequestActor;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;

test('it returns the authenticated application user', function (): void {
    $user = User::factory()->make();
    $request = Request::create('/app/dashboard');
    $request->setUserResolver(static fn (): User => $user);

    expect(RequestActor::from($request))->toBe($user);
});
test('it throws when no authenticated application user is available', function (): void {
    $request = Request::create('/app/dashboard');
    $request->setUserResolver(static fn (): null => null);

    $this->expectException(AuthenticationException::class);

    RequestActor::from($request);
});
test('it throws when the authenticated user is not the application user model', function (): void {
    $request = Request::create('/app/dashboard');
    $request->setUserResolver(static fn (): GenericUser => new GenericUser(['id' => 1, 'email' => 'generic@example.com']));

    $this->expectException(AuthenticationException::class);

    RequestActor::from($request);
});
