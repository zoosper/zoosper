<?php

declare(strict_types=1);

/**
 * Verify frontend static assets are available from public/static.
 */

$basePath = require __DIR__ . '/bootstrap.php';
$options = getopt('', ['theme::']);
$theme = isset($options['theme']) ? trim((string) $options['theme']) : 'default';

$sourceCss = $basePath . '/themes/' . $theme . '/assets/css/app.css';
$publicCss = $basePath . '/public/static/themes/' . $theme . '/assets/css/app.css';

print "Zoosper static asset verification\n";
print "=================================\n\n";

$failed = false;
foreach ([
    'source_css' => $sourceCss,
    'public_css' => $publicCss,
] as $label => $path) {
    $exists = is_file($path);
    print '- ' . $label . ': ' . ($exists ? 'ok' : 'missing') . ' (' . $path . ')' . PHP_EOL;
    $failed = $failed || !$exists;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
