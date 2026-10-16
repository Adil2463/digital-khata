<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Shared\Support\ModuleRegistry;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

final class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        foreach (ModuleRegistry::policyMap(base_path()) as $modelClass => $policyClass) {
            Gate::policy($modelClass, $policyClass);
        }

        $gateFiles = ModuleRegistry::gateFiles(base_path());

        foreach ($gateFiles as $gateFile) {
            if (! is_file($gateFile)) {
                continue;
            }

            require $gateFile;
        }
    }
}
