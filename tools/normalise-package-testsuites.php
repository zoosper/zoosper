<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$phpunitFile = $basePath . '/phpunit.xml';
$dryRun = in_array('--dry-run', $argv, true);

print "Zoosper package testsuite normalisation\n";
print "======================================\n\n";
print 'Mode: ' . ($dryRun ? 'dry-run' : 'apply') . PHP_EOL;

if (!is_file($phpunitFile)) {
    fwrite(STDERR, "ERROR: phpunit.xml not found.\n");
    exit(1);
}

$xml = (string) file_get_contents($phpunitFile);
$oldLine = '<directory>packages/*/tests</directory>';
$newLine = '<directory>packages/*/tests/Unit</directory>';

if (str_contains($xml, $newLine) && !str_contains($xml, $oldLine)) {
    print "packages/*/tests/Unit is already configured.\n";
    print "Result: OK\n";
    exit(0);
}

if (!str_contains($xml, $oldLine)) {
    fwrite(STDERR, "ERROR: Broad package tests entry was not found in phpunit.xml.\n");
    exit(1);
}

print "Will replace packages/*/tests with packages/*/tests/Unit.\n";

if ($dryRun) {
    print "\nDry-run only. No files changed.\n";
    exit(0);
}

$updated = str_replace($oldLine, $newLine, $xml);
file_put_contents($phpunitFile, $updated);

print "\nUpdated phpunit.xml.\n";
print "Result: OK\n";
