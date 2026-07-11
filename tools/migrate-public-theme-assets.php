<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper public theme asset migration\n";
print "====================================\n\n";

$copies = [
    'public/themes/admin/default/assets/css/admin.css' => 'public/assets/admin/css/admin.css',
    'public/themes/default/assets/css/app.css' => 'public/static/themes/default/assets/css/app.css',
];

$copied = 0;
foreach ($copies as $sourceRel => $targetRel) {
    $source = $basePath . '/' . $sourceRel;
    $target = $basePath . '/' . $targetRel;
    if (!is_file($source)) {
        print '- missing source: ' . $sourceRel . PHP_EOL;
        continue;
    }

    $targetDir = dirname($target);
    if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        print '- unable to create target directory: ' . str_replace($basePath . '/', '', $targetDir) . PHP_EOL;
        continue;
    }

    copy($source, $target);
    $copied++;
    print '- copied: ' . $sourceRel . ' -> ' . $targetRel . PHP_EOL;
}

print "\nCopied: {$copied}\nResult: OK\n";
