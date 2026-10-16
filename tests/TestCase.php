<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Support\CreatesUserActionContext;
use Tests\Support\InteractsWithConsoleCommands;
use Tests\Support\InteractsWithModuleFixtures;
use Tests\Support\InteractsWithModuleGeneration;
use Tests\Support\InteractsWithPersistedSessions;
use Tests\Support\InteractsWithTemporaryDirectories;

abstract class TestCase extends BaseTestCase
{
    use CreatesUserActionContext;
    use InteractsWithConsoleCommands;
    use InteractsWithModuleFixtures;
    use InteractsWithModuleGeneration;
    use InteractsWithPersistedSessions;
    use InteractsWithTemporaryDirectories;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }
}
