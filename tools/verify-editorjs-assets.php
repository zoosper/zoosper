<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper Editor.js local asset verification\n";
print "==========================================\n\n";

$checks = [
    'package.json' => is_file($basePath . '/package.json'),
    'vite config' => is_file($basePath . '/vite.admin-editor.config.js'),
    'editor entry source' => is_file($basePath . '/assets/admin/editor/zoosper-editorjs-entry.js'),
    'public editor bundle' => is_file($basePath . '/public/assets/admin/js/editorjs.bundle.js'),
    'admin adapter script' => is_file($basePath . '/public/assets/admin/js/zoosper-content-editor.js'),
    'admin assets config' => is_file($basePath . '/app/zoosper-admin/config/admin_assets.php'),
];

$package = json_decode((string) file_get_contents($basePath . '/package.json'), true);
$checks['@editorjs/editorjs dependency'] = isset($package['dependencies']['@editorjs/editorjs']);
$checks['vite dev dependency'] = isset($package['devDependencies']['vite']);

$assetsConfig = require $basePath . '/app/zoosper-admin/config/admin_assets.php';
$paths = array_map(static fn (array $asset): string => (string) ($asset['path'] ?? ''), $assetsConfig['assets'] ?? []);
$checks['admin loads editorjs bundle'] = in_array('/assets/admin/js/editorjs.bundle.js', $paths, true);
$checks['admin loads editor adapter'] = in_array('/assets/admin/js/zoosper-content-editor.js', $paths, true);

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
