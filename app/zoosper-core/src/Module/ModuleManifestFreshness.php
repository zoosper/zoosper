<?php

declare(strict_types=1);

namespace Zoosper\Core\Module;

/** Produces deterministic freshness stamps for compiled module discovery. */
final readonly class ModuleManifestFreshness
{
    public function __construct(private string $basePath)
    {
    }

    public function composerLockHash(): string
    {
        $path = rtrim($this->basePath, '/\\') . '/composer.lock';

        return is_file($path) ? (hash_file('sha256', $path) ?: '') : '';
    }

    public function firstPartyModulesHash(): string
    {
        $base = rtrim($this->basePath, '/\\');
        $files = array_merge(
            glob($base . '/app/*/module.php') ?: [],
            glob($base . '/modules/*/module.php') ?: [],
            glob($base . '/modules/*/*/module.php') ?: [],
        );
        sort($files, SORT_STRING);

        $entries = [];
        foreach ($files as $file) {
            $relative = str_starts_with($file, $base . '/')
                ? substr($file, strlen($base) + 1)
                : $file;
            $entries[] = $relative . ':' . (string) (filemtime($file) ?: 0);
        }

        return hash('sha256', implode("\n", $entries));
    }

    /** @return array{composerLock: string, firstPartyModules: string} */
    public function stamps(): array
    {
        return [
            'composerLock' => $this->composerLockHash(),
            'firstPartyModules' => $this->firstPartyModulesHash(),
        ];
    }
}
