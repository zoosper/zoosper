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

        if (!is_file($path)) {
            return '';
        }

        $hash = hash_file('sha256', $path);

        return $hash === false ? '' : $hash;
    }

    /** @return list<string> */
    private static function files(string $pattern): array
    {
        $files = glob($pattern);

        return $files === false ? [] : $files;
    }

    public function firstPartyModulesHash(): string
    {
        $base = rtrim($this->basePath, '/\\');
        $files = array_merge(
            self::files($base . '/app/*/module.php'),
            self::files($base . '/modules/*/module.php'),
            self::files($base . '/modules/*/*/module.php'),
        );
        sort($files, SORT_STRING);

        $entries = [];
        foreach ($files as $file) {
            $relative = str_starts_with($file, $base . '/')
                ? substr($file, strlen($base) + 1)
                : $file;
            $modifiedAt = filemtime($file);
            $entries[] = $relative . ':' . (string) ($modifiedAt === false ? 0 : $modifiedAt);
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










