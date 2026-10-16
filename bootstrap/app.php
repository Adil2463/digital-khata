<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureAccountCanSignIn;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use App\Modules\Shared\Support\ModuleRegistry;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withEvents([
        app_path('Listeners'),
        ...ModuleRegistry::listenerDirectories(base_path()),
    ])
    ->registered(function (Application $application): void {
        foreach (ModuleRegistry::providerClasses($application->basePath()) as $providerClass) {
            $application->register($providerClass);
        }
    })
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Modules/Shared/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);
        $middleware->redirectGuestsTo(fn (Request $request): string => route('auth.login.create'));
        $middleware->redirectUsersTo(fn (Request $request): string => route('app.dashboard'));
        $middleware->alias(['verified' => EnsureEmailIsVerified::redirectTo('auth.verification.notice')]);
        $middleware->statefulApi();

        $middleware->web(append: [
            EnsureAccountCanSignIn::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            SecurityHeaders::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->api(append: [
            EnsureAccountCanSignIn::class.':sanctum',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // An idle session expires after `session.lifetime`; the next form submit then fails CSRF.
        // Send the user somewhere useful instead of Laravel's bare "Page Expired" response.
        $exceptions->respond(static function (Response $response, Throwable $throwable, Request $request): Response {
            if ($response->getStatusCode() !== 419 || $request->expectsJson()) {
                return $response;
            }

            if ($request->user() === null) {
                return redirect()->route('auth.login.create')
                    ->with('status', 'Your session expired after a period of inactivity. Please log in again.');
            }

            return back()->with('error', 'This page expired. Please try again.');
        });
    })->create();
