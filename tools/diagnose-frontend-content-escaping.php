<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper frontend content escaping diagnostics\n";
print "============================================\n\n";

$paths = [
    'themes/default/templates/layout.php',
    'themes/default/templates/layout.latte',
];

foreach ($paths as $path) {
    $absolute = $basePath . '/' . $path;
    print $path . ': ' . (is_file($absolute) ? 'exists' : 'missing') . PHP_EOL;
    if (!is_file($absolute)) {
        continue;
    }

    $content = (string) file_get_contents($absolute);
    print '- contains noescape: ' . (str_contains($content, 'noescape') ? 'yes' : 'no') . PHP_EOL;
    print '- content escaped with e(): ' . (str_contains($content, '$e($content') ? 'yes' : 'no') . PHP_EOL;
    print '- content escaped with htmlspecialchars(): ' . (str_contains($content, 'htmlspecialchars($content') ? 'yes' : 'no') . PHP_EOL;
}
