<?php

declare(strict_types=1);

namespace App\Modules\Shop\Providers;

use App\Modules\Shop\Console\Commands\InstallShopCommand;
use Illuminate\Support\ServiceProvider;

final class ModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([InstallShopCommand::class]);
        }
    }
}
