<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper page view template selection diagnostics\n";
print "===============================================\n\n";

$candidates = [
    'themes/default/templates/modules/zoosper-page/page/view.latte',
    'themes/default/templates/modules/zoosper-page/page/view.php',
    'app/zoosper-page/resources/views/page/view.latte',
    'app/zoosper-page/resources/views/page/view.php',
];

foreach ($candidates as $candidate) {
    $absolute = $basePath . '/' . $candidate;
    print '- ' . $candidate . ': ' . (is_file($absolute) ? 'exists' : 'missing') . PHP_EOL;
}

print "\nTemplateRenderer checks theme module overrides before module resource views.\n";
