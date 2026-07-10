<?php

declare(strict_types=1);

/**
 * Verify AdminLayout logout navigation wiring without starting a browser session.
 *
 * This read-only tool does not print or log OTPs, TOTP secrets, recovery-code
 * plaintext, reset tokens, SMTP passwords, payment data or session IDs.
 */

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin logout navigation verification\n";
print "===========================================\n\n";

$checks = [
    'Zoosper\\Admin\\Layout\\AdminLayout',
    'Zoosper\\Admin\\Navigation\\AdminMenu',
    'Zoosper\\Core\\Config\\ConfigRepository',
];

$failed = false;
foreach ($checks as $class) {
    $exists = class_exists($class);
    print '- ' . $class . ': ' . ($exists ? 'ok' : 'missing') . PHP_EOL;
    $failed = $failed || !$exists;
}

$layoutFile = $basePath . '/app/zoosper-admin/src/Layout/AdminLayout.php';
$contents = is_file($layoutFile) ? (string) file_get_contents($layoutFile) : '';
$hasLogoutForm = str_contains($contents, 'admin-nav-logout-form') && str_contains($contents, "method=\"post\"");
print '- POST logout form markup: ' . ($hasLogoutForm ? 'ok' : 'missing') . PHP_EOL;
$failed = $failed || !$hasLogoutForm;

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
