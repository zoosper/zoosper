<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin user locale hydration diagnostics\n";
print "===============================================\n\n";

foreach (['class AdminUser', 'new AdminUser(', "['locale']", 'locale:'] as $needle) {
    print $needle . ': ' . (find_file_containing($basePath, $needle) ?? 'not found') . PHP_EOL;
}

function find_file_containing(string $basePath, string $needle): ?string
{
    foreach ([$basePath . '/app', $basePath . '/modules'] as $root) {
        if (!is_dir($root)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            $path = $file->getPathname();
            if (str_contains((string) file_get_contents($path), $needle)) {
                return $path;
            }
        }
    }

    return null;
}
