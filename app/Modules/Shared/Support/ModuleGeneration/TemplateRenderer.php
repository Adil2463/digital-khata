<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support\ModuleGeneration;

use Illuminate\Filesystem\Filesystem;
use PhpToken;
use RuntimeException;

final readonly class TemplateRenderer
{
    public function __construct(
        private Filesystem $filesystem,
    ) {}

    /**
     * @param  array<string, string>  $tokens
     */
    public function render(string $stubPath, array $tokens): string
    {
        if (! $this->filesystem->exists($stubPath)) {
            throw new RuntimeException('Stub file not found: '.$stubPath);
        }

        $contents = $this->filesystem->get($stubPath);

        $rendered = preg_replace_callback('/{{\s*([A-Za-z][A-Za-z0-9]*)\s*}}/', static function (array $match) use ($tokens, $stubPath): string {
            $key = $match[1];

            return $tokens[$key] ?? throw new RuntimeException(sprintf('Missing template token "%s" in %s.', $key, $stubPath));
        }, $contents);

        $rendered ?? throw new RuntimeException('Could not render stub: '.$stubPath);

        if (! str_starts_with($rendered, '<?php')) {
            return $rendered;
        }

        $this->validateImports($rendered, $stubPath);

        return PhpUseStatementSorter::sort($rendered);
    }

    private function validateImports(string $contents, string $stubPath): void
    {
        $names = [];
        $expectsName = false;
        foreach (PhpToken::tokenize($contents, TOKEN_PARSE) as $phpToken) {
            if ($phpToken->isIgnorable()) {
                continue;
            }

            if ($expectsName && $phpToken->id === T_STRING) {
                $names[mb_strtolower($phpToken->text)] = true;
            }

            $expectsName = false;
            if (in_array($phpToken->id, [T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM], true)) {
                $expectsName = true;
            }
        }

        // Rendered stubs have one top-level class import per line; indented trait
        // uses and closure captures are deliberately outside this check.
        preg_match_all('/^use ([\\\\A-Za-z_][\\\\A-Za-z0-9_]*)(?: as ([A-Za-z_][A-Za-z0-9_]*))?;$/m', $contents, $imports, PREG_SET_ORDER);
        foreach ($imports as $import) {
            $segments = explode('\\', $import[1]);
            $name = $import[2] ?? $segments[array_key_last($segments)];
            $normalized = mb_strtolower($name);
            if (isset($names[$normalized])) {
                throw new RuntimeException(sprintf('Generated PHP name "%s" conflicts with an import in %s. Choose a different module or page name.', $name, $stubPath));
            }

            $names[$normalized] = true;
        }
    }
}
