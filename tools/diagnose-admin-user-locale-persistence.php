<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$repositoryPath = find_file_containing($basePath, 'class AdminUserRepository');

print "Zoosper admin user locale persistence diagnostics\n";
print "=================================================\n\n";

foreach ([$controllerPath, $repositoryPath] as $path) {
    if ($path === null || !is_file($path)) {
        continue;
    }

    $source = (string) file_get_contents($path);
    print relative_path($basePath, $path) . PHP_EOL;
    print '- has locale field/rendering: ' . ((str_contains($source, 'name="locale"') || str_contains($source, 'renderAdminLocaleField(')) ? 'yes' : 'no') . PHP_EOL;
    print '- has submitted locale: ' . ((str_contains($source, "\$submitted['locale']") || str_contains($source, '$submitted["locale"]')) ? 'yes' : 'no') . PHP_EOL;
    print '- has normaliseAdminLocale: ' . (str_contains($source, 'normaliseAdminLocale(') ? 'yes' : 'no') . PHP_EOL;
    print '- has SQL locale write: ' . ((str_contains($source, 'locale = :locale') || str_contains($source, ', locale')) ? 'yes' : 'no') . PHP_EOL;
    print PHP_EOL;
}

function find_file_containing(string $basePath, string $needle): ?string
{
    foreach ([$basePath . '/app', $basePath . '/modules'] as $root) {
        if (!is_dir($root)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->getExtension() === 'php') {
                $path = $file->getPathname();
                if (str_contains((string) file_get_contents($path), $needle)) {
                    return $path;
                }
            }
        }
    }
    return null;
}

function relative_path(string $basePath, string $path): string
{
    return str_starts_with($path, $basePath . '/') ? substr($path, strlen($basePath) + 1) : $path;
}
