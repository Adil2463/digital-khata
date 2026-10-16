<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

/**
 * @mixin TestCase
 */
trait InteractsWithModuleGeneration
{
    protected function createTemporaryModuleGenerationBasePath(): string
    {
        $basePath = $this->temporaryDirectoryPath('generate-module');

        $this->ensureDirectory($basePath.'/app/Modules');
        $this->ensureDirectory($basePath.'/resources/js/modules');
        $this->ensureDirectory($basePath.'/tests/Feature');

        return $basePath;
    }

    protected function assertMigrationFileExists(string $basePath, string $tableName): void
    {
        $migrationFiles = $this->migrationFiles($basePath, $tableName);

        $this->assertNotEmpty(
            $migrationFiles,
            sprintf('Expected migration file for table [%s] to be generated.', $tableName),
        );
    }

    protected function assertNoMigrationFileExists(string $basePath, string $tableName): void
    {
        $migrationFiles = $this->migrationFiles($basePath, $tableName);

        $this->assertEmpty(
            $migrationFiles,
            sprintf('Did not expect migration file for table [%s] to be generated.', $tableName),
        );
    }

    protected function assertMigrationCount(string $basePath, string $tableName, int $expectedCount): void
    {
        $this->assertCount(
            $expectedCount,
            $this->migrationFiles($basePath, $tableName),
            sprintf('Expected %d migration file(s) for table [%s].', $expectedCount, $tableName),
        );
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    protected function runGenerateCommand(array $arguments): PendingCommand
    {
        $pendingCommand = $this->artisan('generate:module', $arguments);

        if (is_int($pendingCommand)) {
            $this->fail('Expected a PendingCommand instance while running generate:module.');
        }

        return $pendingCommand;
    }

    /**
     * @return list<string>
     */
    private function migrationFiles(string $basePath, string $tableName): array
    {
        $migrationFiles = glob($basePath.sprintf('/database/migrations/*_create_%s_table.php', $tableName));

        return is_array($migrationFiles) ? $migrationFiles : [];
    }
}
