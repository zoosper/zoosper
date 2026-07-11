<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$config = require $basePath . '/app/zoosper-admin/config/admin_assets.php';

print "Zoosper admin asset cache-busting verification\n";
print "==============================================\n\n";

$required = [
    '/assets/admin/css/admin.css',
    '/assets/admin/css/zoosper-admin-messages.css',
    '/assets/admin/css/zoosper-content-editor.css',
    '/assets/admin/js/editorjs.bundle.js',
    '/assets/admin/js/zoosper-content-editor.js',
];
$paths = array_map(static fn (array $asset): string => (string) ($asset['path'] ?? ''), $config['assets'] ?? []);
$failed = false;
foreach ($required as $requiredPath) {
    $matches = array_values(array_filter($paths, static fn (string $path): bool => str_starts_with($path, $requiredPath . '?v=')));
    $ok = $matches !== [];
    print '- ' . $requiredPath . ' versioned: ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
