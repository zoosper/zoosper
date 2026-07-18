<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$phpunitFile = $basePath . '/phpunit.xml';
$xml = is_file($phpunitFile) ? (string) file_get_contents($phpunitFile) : '';

print "Zoosper package testsuite verification\n";
print "======================================\n\n";

$checks = [
    'phpunit.xml exists' => is_file($phpunitFile),
    'packages/*/tests/Unit configured' => str_contains($xml, 'packages/*/tests/Unit'),
    'broad packages/*/tests entry absent' => !str_contains($xml, '<directory>packages/*/tests</directory>'),
    'packages/zoosper-media/tests/Unit exists' => is_dir($basePath . '/packages/zoosper-media/tests/Unit'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
