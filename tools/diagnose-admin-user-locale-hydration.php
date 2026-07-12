<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin user locale hydration diagnostics\n";
print "===============================================\n\n";

print "AdminUser model candidates:\n";
foreach (php_files($basePath) as $path) {
    $contents = (string) file_get_contents($path);
    if (basename($path) === 'AdminUser.php' || preg_match('/\bclass\s+AdminUser\b/', $contents) === 1) {
        print '- ' . relative_path($basePath, $path) . PHP_EOL;
    }
}

print "\nAdminUser hydration candidates:\n";
foreach (php_files($basePath) as $path) {
    $contents = (string) file_get_contents($path);
    if (str_contains($contents, 'new AdminUser(')) {
        print '- ' . relative_path($basePath, $path) . PHP_EOL;
    }
}

/** @return list<string> */
function php_files(string $basePath): array
{
    $results = [];
    foreach ([$basePath . '/app', $basePath . '/modules'] as $root) {
        if (!is_dir($root)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->getExtension() === 'php') {
                $results[] = $file->getPathname();
            }
        }
    }

    return $results;
}

function relative_path(string $basePath, string $path): string
{
    return str_starts_with($path, $basePath . '/') ? substr($path, strlen($basePath) + 1) : $path;
}
