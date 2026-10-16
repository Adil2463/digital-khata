<?php

declare(strict_types=1);

test('the test environment can never boot from a cached configuration, route table, or event map', function (): void {
    $application = $this->app;

    expect($application->configurationIsCached())->toBeFalse()
        ->and($application->routesAreCached())->toBeFalse()
        ->and($application->eventsAreCached())->toBeFalse();

    $bootCachePaths = [
        $application->getCachedConfigPath() => 'bootstrap/cache/testing/config.php',
        $application->getCachedEventsPath() => 'bootstrap/cache/testing/events.php',
        $application->getCachedRoutesPath() => 'bootstrap/cache/testing/routes.php',
    ];

    foreach ($bootCachePaths as $resolvedPath => $expectedRelativePath) {
        expect($resolvedPath)->toBe($application->basePath($expectedRelativePath))
            ->and(is_file($resolvedPath))->toBeFalse();
    }
});
