<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Read by Laravel's TrustProxies middleware on every request. List the
    | comma-separated addresses or CIDR ranges of the proxy that terminates
    | TLS in front of the application, or "*" to trust the calling address.
    | Leave it unset when clients connect to the web server directly, so
    | forwarded headers from clients are ignored.
    |
    */

    'proxies' => env('TRUSTED_PROXIES'),

];
