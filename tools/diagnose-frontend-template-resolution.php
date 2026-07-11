<?php

declare(strict_types=1);

/**
 * Diagnose default frontend template resolution priority.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$templates = [
    'layout' => [
        'preferred' => 'themes/default/templates/layout.latte',
        'fallback' => 'themes/default/templates/layout.php',
    ],
    'zoosper-page::page/view' => [
        'preferred' => 'app/zoosper-page/resources/views/page/view.latte',
        'fallback' => 'app/zoosper-page/resources/views/page/view.php',
    ],
];

print "Zoosper frontend template resolution diagnostics\n";
print "================================================\n\n";

foreach ($templates as $logicalName => $paths) {
    print $logicalName . PHP_EOL;
    foreach ($paths as $type => $path) {
        print '  - ' . $type . ': ' . (is_file($basePath . '/' . $path) ? 'yes' : 'no') . ' (' . $path . ')' . PHP_EOL;
    }
}
