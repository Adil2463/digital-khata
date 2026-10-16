<!DOCTYPE html>
{{-- `HandleAppearance` resolves the appearance from its cookie, so the color scheme
     is part of the server-rendered markup: there is no boot script to run and nothing
     for the client to re-apply once the bundle evaluates. --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'light') === 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Consumed by resources/js/app.ts so Inertia can nonce the style elements it injects --}}
        <meta name="csp-nonce" content="{{ Vite::cspNonce() }}">

        <title data-inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        {{-- Self-hosted fonts from `vite.config.ts`: a preload plus inline `@font-face`
             rules, so the font request starts before any stylesheet is parsed. --}}
        @fonts

        {{-- The stylesheet is listed first and is a Vite entry of its own, so it is a
             render-blocking `<link>` in dev and production alike. Bundling it through
             `app.ts` instead would let the server-rendered markup paint unstyled and
             reflow once the JS evaluated. --}}
        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
