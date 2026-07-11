<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/public-webroot-policy.php';

$options = getopt('', ['yes']);
if (!isset($options['yes'])) {
    fwrite(STDERR, "Refusing to quarantine without --yes. Review audit output first.\n");
    exit(2);
}

$policy = zoosper_public_policy_load($basePath);
$findings = zoosper_public_scan($basePath, $policy);
$publicPath = $basePath . '/' . trim((string) ($policy['public_path'] ?? 'public'), '/');
$quarantineBase = $basePath . '/' . trim((string) ($policy['quarantine_path'] ?? 'var/quarantine/public-webroot'), '/');
$quarantinePath = $quarantineBase . '/' . gmdate('Ymd-His');

print "Zoosper public webroot quarantine\n";
print "=================================\n\n";

if ($findings === []) {
    print "No suspicious files detected.\nResult: OK\n";
    exit(0);
}

if (!is_dir($quarantinePath) && !mkdir($quarantinePath, 0770, true) && !is_dir($quarantinePath)) {
    fwrite(STDERR, "Unable to create quarantine path: {$quarantinePath}\n");
    exit(2);
}

$moved = 0;
foreach ($findings as $finding) {
    $relative = (string) $finding['path'];
    $source = $publicPath . '/' . ltrim($relative, '/');
    if (!is_file($source)) {
        continue;
    }

    $destination = $quarantinePath . '/' . ltrim($relative, '/');
    $destinationDir = dirname($destination);
    if (!is_dir($destinationDir) && !mkdir($destinationDir, 0770, true) && !is_dir($destinationDir)) {
        fwrite(STDERR, "Unable to create quarantine directory: {$destinationDir}\n");
        continue;
    }

    if (rename($source, $destination)) {
        $moved++;
        print '- moved ' . $relative . ' -> ' . str_replace($basePath . '/', '', $destination) . PHP_EOL;
    }
}

print "\nMoved: {$moved}\n";
print "Quarantine: " . str_replace($basePath . '/', '', $quarantinePath) . PHP_EOL;
print "Result: OK\n";
