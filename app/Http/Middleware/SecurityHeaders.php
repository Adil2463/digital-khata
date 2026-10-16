<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeaders
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Also applied by `@vite` to the tags it renders, and read by the root view.
        $nonce = Vite::useCspNonce();

        $response = $next($request);

        $isLocalEnvironment = App::isLocal();

        $scriptSrc = $isLocalEnvironment
            ? "script-src 'self' 'unsafe-inline' 'unsafe-eval' http: https:"
            : sprintf("script-src 'self' 'nonce-%s'", $nonce);
        // Locally the stylesheet and fonts are served by the Vite dev server rather than
        // this origin, so the scheme sources are needed for the same reason `script-src`
        // needs them. In production every asset, fonts included, is served from `'self'`.
        $styleSrc = $isLocalEnvironment
            ? "style-src 'self' 'unsafe-inline' http: https:"
            : sprintf("style-src 'self' 'nonce-%s'", $nonce);
        // Vue renders `v-show` and bound styles as `style` attributes in server-rendered
        // markup, and a nonce cannot cover an attribute. Attributes cannot run script, so
        // they are allowed while `<style>` elements still require the nonce.
        $styleSrcAttr = "style-src-attr 'unsafe-inline'";
        $fontSrc = $isLocalEnvironment
            ? "font-src 'self' http: https:"
            : "font-src 'self'";

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",
            $scriptSrc,
            $styleSrc,
            $styleSrcAttr,
            $fontSrc,
            "img-src 'self' data: blob:",
            "connect-src 'self' ws: wss: http: https:",
            "form-action 'self'",
        ]));
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
