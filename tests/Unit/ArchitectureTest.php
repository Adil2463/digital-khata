<?php

declare(strict_types=1);

arch('first-party namespaces use strict types')
    ->expect(['App', 'Database', 'Tests'])
    ->toUseStrictTypes();

arch('application code excludes debugging calls')
    ->expect('App')
    ->not->toUse(['dd', 'dump', 'ray', 'var_dump']);

arch('application code follows the security preset')
    ->preset()
    ->security()
    ->ignoring('sha1');
