<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$config = is_file($basePath . '/config/editor.php') ? require $basePath . '/config/editor.php' : [];
$bundle = $basePath . (string) ($config['editorjs']['bundle_path'] ?? '/public/assets/admin/js/editorjs.bundle.js');
$bundle = str_replace($basePath . '/assets/', $basePath . '/public/assets/', $bundle);

print "Zoosper Editor.js local asset diagnostics\n";
print "========================================\n\n";
print 'bundle_path          : ' . ($config['editorjs']['bundle_path'] ?? 'missing') . PHP_EOL;
print 'bundle_source        : ' . ($config['editorjs']['bundle_source'] ?? 'missing') . PHP_EOL;
print 'build_command        : ' . ($config['editorjs']['build_command'] ?? 'missing') . PHP_EOL;
print 'bundle_file_exists   : ' . (is_file($bundle) ? 'yes' : 'no') . PHP_EOL;
print 'node_modules_exists  : ' . (is_dir($basePath . '/node_modules') ? 'yes' : 'no') . PHP_EOL;
print 'package_lock_exists  : ' . (is_file($basePath . '/package-lock.json') ? 'yes' : 'no') . PHP_EOL;
