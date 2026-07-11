<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$publicThemes = $basePath . '/public/themes';

print "Zoosper public theme asset audit\n";
print "================================\n\n";

if (!file_exists($publicThemes)) {
    print "public/themes is absent.\n\nResult: OK\n";
    exit(0);
}

$files = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($publicThemes, FilesystemIterator::SKIP_DOTS),
);
foreach ($iterator as $item) {
    if ($item->isFile()) {
        $files[] = str_replace($basePath . '/', '', $item->getPathname());
    }
}

print "public/themes exists and should be removed after assets are migrated.\n";
foreach ($files as $file) {
    print '- ' . $file . PHP_EOL;
}

print "\nResult: REVIEW_REQUIRED\n";
exit(1);
