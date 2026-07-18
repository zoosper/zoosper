<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$overwrite = in_array('--overwrite', $argv, true);

$generator = new \Zoosper\Core\Composer\ModuleComposerManifestGenerator($basePath);
$written = $generator->generate($overwrite);

print "Zoosper module composer manifest generation\n";
print "===========================================\n\n";

if ($written === []) {
    print "No module composer.json files were written. Existing files were left untouched.\n";
} else {
    foreach ($written as $file) {
        print '- wrote ' . $file . PHP_EOL;
    }
}

print "\nUse --overwrite to refresh existing generated manifests.\n";
