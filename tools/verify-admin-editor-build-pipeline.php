<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin editor build pipeline verification\n";
print "================================================\n\n";

$viteConfigPath = $basePath . '/vite.admin-editor.config.js';
$viteConfig = is_file($viteConfigPath) ? (string) file_get_contents($viteConfigPath) : '';
$packagePath = $basePath . '/package.json';
$package = is_file($packagePath) ? json_decode((string) file_get_contents($packagePath), true) : [];

$checks = [
    'vite config exists' => is_file($viteConfigPath),
    'vite publicDir disabled' => str_contains($viteConfig, 'publicDir: false'),
    'vite outDir targets admin js' => str_contains($viteConfig, "outDir: 'public/assets/admin/js'") || str_contains($viteConfig, 'outDir: "public/assets/admin/js"'),
    'build script exists' => isset($package['scripts']['build:admin-editor']),
    '@editorjs/editorjs dependency' => isset($package['dependencies']['@editorjs/editorjs']),
    'vite dev dependency' => isset($package['devDependencies']['vite']),
    'editor entry source exists' => is_file($basePath . '/assets/admin/editor/zoosper-editorjs-entry.js'),
    'public bundle exists' => is_file($basePath . '/public/assets/admin/js/editorjs.bundle.js'),
    'recursive build artefact absent' => !file_exists($basePath . '/public/assets/admin/js/assets'),
    'node_modules not public' => !is_dir($basePath . '/public/node_modules'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
