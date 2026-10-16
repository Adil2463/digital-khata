<?php

declare(strict_types=1);

use App\Modules\Shared\Support\ModuleRegistry;

$moduleChannelFiles = ModuleRegistry::channelFiles(base_path());

foreach ($moduleChannelFiles as $moduleChannelFile) {
    require $moduleChannelFile;
}
