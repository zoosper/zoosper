<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$options = getopt('', ['yes']);
if (!isset($options['yes'])) {
    fwrite(STDERR, "Refusing to remove public/themes without --yes. Run migrate-public-theme-assets.php first.\n");
    exit(2);
}

$target = $basePath . '/public/themes';
print "Zoosper public/themes removal\n";
print "=============================\n\n";

if (!file_exists($target)) {
    print "public/themes is already absent.\nResult: OK\n";
    exit(0);
}

removeRecursive($target);
print "Removed public/themes.\nResult: OK\n";

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
        $item->isDir() && !$item->isLink() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
    }

    @rmdir($path);
}
