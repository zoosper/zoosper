<?php

declare(strict_types=1);

/**
 * Diagnose discovered modules and extension locations.
 */

$basePath = require __DIR__ . '/bootstrap.php';
$modules = new \Zoosper\Core\Module\ModuleRegistry($basePath);

print "Zoosper module discovery diagnostics\n";
print "====================================\n\n";
print "Custom modules can be placed in:\n";
print "- modules/<module>/module.php\n";
print "- modules/<vendor>/<module>/module.php\n\n";

foreach ($modules->allModules() as $module) {
    $order = (string) ($module->metadata['sort_order'] ?? '100');
    print '- ' . $module->name . ' | enabled=' . ($module->enabled ? 'yes' : 'no') . ' | sort_order=' . $order . ' | path=' . $module->path . PHP_EOL;
}
