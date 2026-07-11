<?php

declare(strict_types=1);

/**
 * Verify enabled module dependencies declared in module.php metadata.
 */

$basePath = require __DIR__ . '/bootstrap.php';
$registry = new \Zoosper\Core\Module\ModuleRegistry($basePath);
$validator = new \Zoosper\Core\Module\ModuleDependencyValidator();

print "Zoosper module dependency verification\n";
print "======================================\n\n";

try {
    $modules = $registry->enabledModules();
    foreach ($modules as $module) {
        $depends = $validator->depends($module);
        print '- ' . $module->name . ': ' . ($depends === [] ? 'no dependencies' : implode(', ', $depends)) . PHP_EOL;
    }

    print "\nResult: OK\n";
    exit(0);
} catch (Throwable $exception) {
    print "Result: FAIL\n";
    print $exception->getMessage() . PHP_EOL;
    exit(2);
}
