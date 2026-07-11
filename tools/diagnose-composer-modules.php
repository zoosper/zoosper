<?php

declare(strict_types=1);

/**
 * Diagnose Composer-installed Zoosper module discovery.
 */

$basePath = require __DIR__ . '/bootstrap.php';
$discovery = new \Zoosper\Core\Module\ComposerModuleDiscovery($basePath);

print "Zoosper Composer module discovery diagnostics\n";
print "=============================================\n\n";
print "Composer marketplace modules are discovered when packages use:\n";
print "- composer.json type: zoosper-module\n";
print "- optional extra.zoosper.module: module.php\n\n";

$files = $discovery->moduleFiles();
if ($files === []) {
    print "No Composer-installed Zoosper modules discovered.\n";
    print "This is OK if no marketplace modules are installed yet.\n";
    exit(0);
}

foreach ($files as $file) {
    print '- ' . $file . PHP_EOL;
}
