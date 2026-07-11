<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$targets = [
    $basePath . '/public/assets/admin/js/assets',
];

print "Zoosper admin editor build artefact cleaner\n";
print "===========================================\n\n";

$removed = 0;
foreach ($targets as $target) {
    if (!file_exists($target)) {
        print '- missing: ' . str_replace($basePath . '/', '', $target) . PHP_EOL;
        continue;
    }

    removeRecursive($target);
    $removed++;
    print '- removed: ' . str_replace($basePath . '/', '', $target) . PHP_EOL;
}

print "\nRemoved: {$removed}\nResult: OK\n";

function removeRecursive(string $path): void
{
    if (is_file($path) || is_link($path)) {
        @unlink($path);
        return;
    }

    if (!is_dir($path)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );

    foreach ($iterator as $item) {
        $item->isDir() && !$item->isLink()
            ? @rmdir($item->getPathname())
            : @unlink($item->getPathname());
    }

    @rmdir($path);
}
