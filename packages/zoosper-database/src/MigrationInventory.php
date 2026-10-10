<?php

declare(strict_types=1);

namespace Zoosper\Database;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

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
                $migrationPaths = glob($root . '/*.php');
                if ($migrationPaths === false) {
                    throw new RuntimeException('Unable to enumerate root migrations: ' . $root);
                }
                foreach ($migrationPaths as $migrationPath) {
                    $this->register($files, $migrationPath);
                }
                continue;
            }
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
            foreach ($iterator as $file) {
                if (!$file instanceof SplFileInfo) {
                    continue;
                }
                if (!$file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $migrationPath = $file->getPathname();
                if (!str_contains(str_replace('\\', '/', $migrationPath), '/database/migrations/')) {
                    continue;
                }
                $this->register($files, $migrationPath);
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
