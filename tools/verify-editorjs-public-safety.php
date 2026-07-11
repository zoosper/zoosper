<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/public-webroot-policy.php';
$policy = zoosper_public_policy_load($basePath);
$findings = array_values(array_filter(
    zoosper_public_scan($basePath, $policy),
    static fn (array $finding): bool => str_contains((string) ($finding['path'] ?? ''), 'editorjs') || str_contains((string) ($finding['path'] ?? ''), 'zoosper-content-editor')
));

print "Zoosper Editor.js public asset safety verification\n";
print "=================================================\n\n";

if ($findings === []) {
    print "No Editor.js public asset safety findings.\n\nResult: OK\n";
    exit(0);
}

foreach ($findings as $finding) {
    print '- ' . strtoupper((string) $finding['severity']) . ': ' . $finding['path'] . ' - ' . $finding['reason'] . PHP_EOL;
}

print "\nResult: REVIEW_REQUIRED\n";
exit(1);
