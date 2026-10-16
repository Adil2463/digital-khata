<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Testing\PendingCommand;

trait InteractsWithConsoleCommands
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public function pendingArtisan(string $command, array $parameters = []): PendingCommand
    {
        $pendingCommand = $this->artisan($command, $parameters);

        if (is_int($pendingCommand)) {
            $this->fail(sprintf('Expected a PendingCommand instance while running %s.', $command));
        }

        return $pendingCommand;
    }
}
