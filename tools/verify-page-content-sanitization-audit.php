<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper page content sanitisation audit verification\n";
print "====================================================\n\n";

$checks = [
    'PageContentSanitizationResult' => class_exists(\Zoosper\Page\Sanitization\PageContentSanitizationResult::class),
    'PageContentSanitizationAuditor' => class_exists(\Zoosper\Page\Sanitization\PageContentSanitizationAuditor::class),
    'audit tool' => is_file($basePath . '/tools/audit-page-content-sanitization.php'),
    'repair tool' => is_file($basePath . '/tools/repair-page-content-sanitization.php'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
