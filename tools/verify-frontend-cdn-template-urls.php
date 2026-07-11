<?php

declare(strict_types=1);

/**
 * Verify that frontend templates can consume CDN/render context helpers.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$files = [
    'themes/default/templates/layout.php',
    'themes/default/templates/page.php',
    'app/zoosper-page/resources/views/page/view.php',
];

print "Zoosper frontend CDN template verification\n";
print "=========================================\n\n";

$failed = false;
foreach ($files as $file) {
    $path = $basePath . '/' . $file;
    $exists = is_file($path);
    $content = $exists ? (string) file_get_contents($path) : '';
    $hasExpected = $file === 'themes/default/templates/layout.php'
        ? str_contains($content, 'staticAsset(') && str_contains($content, 'dynamicForContext(')
        : str_contains($content, 'server-render');

    print '- ' . $file . ': ' . ($exists && $hasExpected ? 'ok' : 'check') . PHP_EOL;
    $failed = $failed || !$exists || !$hasExpected;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
