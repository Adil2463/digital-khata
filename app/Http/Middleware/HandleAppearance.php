<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Appearance;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

final class HandleAppearance
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Read here so `app.blade.php` can put the class on `<html>` before any CSS
        // is parsed. `HandleInertiaRequests` shares the same value as a page prop, so
        // the server-rendered appearance UI agrees with what the document shows.
        View::share('appearance', Appearance::fromCookie($request->cookie(Appearance::COOKIE))->value);

        return $next($request);
    }
}
