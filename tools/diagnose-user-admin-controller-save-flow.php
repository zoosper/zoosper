<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$repositoryPath = find_file_containing($basePath, 'class AdminUserRepository');

print "Zoosper UserAdminController save-flow diagnostics\n";
print "=================================================\n\n";

foreach ([$controllerPath, $repositoryPath] as $path) {
    if ($path === null || !is_file($path)) {
        continue;
    }

    $source = (string) file_get_contents($path);
    print relative_path($basePath, $path) . PHP_EOL;
    print '- references AdminUserSavePipeline: ' . (str_contains($source, 'AdminUserSavePipeline') ? 'yes' : 'no') . PHP_EOL;
    print '- references AdminUserSaveDataFactory: ' . (str_contains($source, 'AdminUserSaveDataFactory') ? 'yes' : 'no') . PHP_EOL;
    print '- references AdminUserCoreWriteDataMapper: ' . (str_contains($source, 'AdminUserCoreWriteDataMapper') ? 'yes' : 'no') . PHP_EOL;
    print '- has locale form field: ' . ((str_contains($source, 'name="locale"') || str_contains($source, "name='locale'")) ? 'yes' : 'no') . PHP_EOL;
    print '- has repository save/update call: ' . (preg_match('/->(?:save|update)\s*\(/', $source) === 1 ? 'yes' : 'no') . PHP_EOL;
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
