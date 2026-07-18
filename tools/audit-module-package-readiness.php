<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper module Composer package readiness audit\n";
print "================================================\n\n";

$moduleFiles = array_merge(
    glob($basePath . '/app/*/module.php') ?: [],
    glob($basePath . '/modules/*/module.php') ?: [],
    glob($basePath . '/modules/*/*/module.php') ?: [],
);
sort($moduleFiles);

$review = 0;
foreach ($moduleFiles as $moduleFile) {
    $moduleDir = dirname($moduleFile);
    $relativeDir = ltrim(str_replace($basePath, '', $moduleDir), '/\\');
    $module = require $moduleFile;
    if (!is_array($module) || ($module['enabled'] ?? true) === false) {
        continue;
    }

    $identity = \Zoosper\Core\Composer\ModulePackageIdentity::fromModule($module, basename($moduleDir));
    $hasSrc = is_dir($moduleDir . '/src');
    $hasConfig = is_dir($moduleDir . '/config');
    $hasComposer = is_file($moduleDir . '/composer.json');

    $status = 'READY_FOR_PACKAGE_MANIFEST';
    if ($identity === null) {
        $status = 'REVIEW_INVALID_MODULE_IDENTITY';
    } elseif (!$hasSrc) {
        $status = 'SKIP_NO_SRC';
    } elseif (!$hasConfig) {
        $status = 'REVIEW_NO_CONFIG';
    } elseif ($hasComposer) {
        $status = 'READY_PACKAGE_MANIFEST_EXISTS';
    }

    if (str_starts_with($status, 'REVIEW')) {
        $review++;
    }

    print '- ' . $relativeDir . PHP_EOL;
    print '  module      : ' . (string) ($module['name'] ?? basename($moduleDir)) . PHP_EOL;
    print '  package     : ' . ($identity?->packageName ?? '(invalid module identity)') . PHP_EOL;
    print '  namespace   : ' . ($identity?->namespace ?? '(invalid module identity)') . PHP_EOL;
    print '  src         : ' . ($hasSrc ? 'yes' : 'no') . PHP_EOL;
    print '  config      : ' . ($hasConfig ? 'yes' : 'no') . PHP_EOL;
    print '  composer    : ' . ($hasComposer ? 'yes' : 'no') . PHP_EOL;
    print '  status      : ' . $status . PHP_EOL . PHP_EOL;
}

print 'Review items: ' . $review . PHP_EOL;
print 'Result: ' . ($review === 0 ? 'OK' : 'REVIEW_REQUIRED') . PHP_EOL;
exit(0);
