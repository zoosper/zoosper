<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/public-webroot-policy.php';

$policy = zoosper_public_policy_load($basePath);
$findings = zoosper_public_scan($basePath, $policy);

print "Zoosper public webroot audit\n";
print "============================\n\n";
print "Public path: " . ($policy['public_path'] ?? 'public') . "\n\n";

if ($findings === []) {
    print "No blocked public files or directories detected.\n\n";
    print "Result: OK\n";
    exit(0);
}

foreach ($findings as $finding) {
    print '- [' . strtoupper((string) $finding['severity']) . '] ' . $finding['path'] . ' - ' . $finding['reason'] . PHP_EOL;
}

print "\nResult: REVIEW_REQUIRED\n";
exit(1);
