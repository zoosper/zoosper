<?php

declare(strict_types=1);

namespace Zoosper\Database;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

/**
 * Discovers PHP migrations and enforces the basename identity currently stored
 * by Migrator. Basenames must remain globally unique until migration history is
 * deliberately upgraded to a module-qualified identity.
 */
final readonly class MigrationInventory
{
    /** @return array<string, string> basename => absolute path */
    public function discover(string $basePath): array
    {
        $files = [];
        foreach (['database/migrations', 'app', 'packages', 'modules'] as $relative) {
            $root = rtrim($basePath, '/\\') . '/' . $relative;
            if (!is_dir($root)) {
                continue;
            }
            if ($relative === 'database/migrations') {
                foreach (glob($root . '/*.php') ?: [] as $path) {
                    $this->register($files, $path);
                }
                continue;
            }
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
            foreach ($iterator as $file) {
                if (!$file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $path = $file->getPathname();
                if (!str_contains(str_replace('\\', '/', $path), '/database/migrations/')) {
                    continue;
                }
                $this->register($files, $path);
            }
        }
        ksort($files);
        return $files;
    }

    /** @param array<string, string> $files */
    private function register(array &$files, string $path): void
    {
        $basename = basename($path);
        if (isset($files[$basename]) && $files[$basename] !== $path) {
            throw new RuntimeException(sprintf(
                'Duplicate migration basename %s: %s and %s. Migrator stores basenames only; rename the newer migration.',
                $basename,
                $files[$basename],
                $path,
            ));
        }
        $files[$basename] = $path;
    }
}
