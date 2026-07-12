<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin user locale preference UI diagnostics\n";
print "===================================================\n\n";

foreach (php_files($basePath) as $path) {
    $contents = (string) file_get_contents($path);
    if ((str_contains($contents, 'AdminUser') || str_contains($contents, 'admin user') || str_contains($contents, 'Admin user'))
        && (str_contains($contents, 'name="email"') || str_contains($contents, "name='email'"))) {
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
