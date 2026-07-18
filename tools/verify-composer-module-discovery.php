<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$modules = (new \Zoosper\Core\Module\ModuleRegistry($basePath))->enabledModules();

print "Zoosper Composer module discovery verification\n";
print "==============================================\n\n";

$names = [];
foreach ($modules as $module) {
    $names[] = $module->name . ' [' . $module->source . '] ' . ltrim(str_replace($basePath, '', $module->path), '/\\');
}

foreach ($names as $line) {
    print '- ' . $line . PHP_EOL;
}

$hasMedia = false;
foreach ($modules as $module) {
    if (in_array($module->name, ['Zoosper_Media', 'zoosper-media'], true)) {
        $hasMedia = true;
        break;
    }
}

print "\nmedia discovered: " . ($hasMedia ? 'yes' : 'no') . PHP_EOL;
print 'Result: ' . ($hasMedia ? 'OK' : 'FAIL') . PHP_EOL;
exit($hasMedia ? 0 : 2);
