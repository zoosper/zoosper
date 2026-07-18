<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$modules = (new \Zoosper\Core\Module\ModuleRegistry($basePath))->enabledModules();

print "Zoosper media package-independent discovery verification\n";
print "========================================================\n\n";

$media = null;
foreach ($modules as $module) {
    print '- ' . $module->name . ' [' . $module->source . '] ' . ltrim(str_replace($basePath, '', $module->path), '/\\') . PHP_EOL;
    if (in_array($module->name, ['Zoosper_Media', 'zoosper-media'], true)) {
        $media = $module;
    }
}

$checks = [
    'packages/zoosper-media exists' => is_dir($basePath . '/packages/zoosper-media'),
    'app/zoosper-media compatibility symlink removed' => !is_link($basePath . '/app/zoosper-media'),
    'app/zoosper-media real directory absent' => !is_dir($basePath . '/app/zoosper-media'),
    'media module discovered' => $media !== null,
    'media module source is package or vendor' => $media !== null && in_array($media->source, ['packages', 'vendor'], true),
];

print "\nChecks\n------\n";
$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
