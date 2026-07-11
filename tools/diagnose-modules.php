<?php

declare(strict_types=1);

/**
 * Diagnose discovered modules, extension locations and dependency metadata.
 */

$basePath = require __DIR__ . '/bootstrap.php';
$modules = new \Zoosper\Core\Module\ModuleRegistry($basePath);
$validator = new \Zoosper\Core\Module\ModuleDependencyValidator();

print "Zoosper module discovery diagnostics\n";
print "====================================\n\n";
print "Custom modules can be placed in:\n";
print "- modules/<module>/module.php\n";
print "- modules/<vendor>/<module>/module.php\n\n";
print "Composer marketplace modules can be installed under vendor/ when composer.json declares type=zoosper-module.\n\n";

foreach ($modules->allModules() as $module) {
    $order = (string) ($module->metadata['sort_order'] ?? '100');
    $depends = $validator->depends($module);
    print '- ' . $module->name
        . ' | enabled=' . ($module->enabled ? 'yes' : 'no')
        . ' | sort_order=' . $order
        . ' | depends=' . ($depends === [] ? '-' : implode(',', $depends))
        . ' | path=' . $module->path
        . PHP_EOL;
}
