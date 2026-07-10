<?php

declare(strict_types=1);

/**
 * Verify 2FA login redirect service dependencies without exposing secrets.
 *
 * This read-only verification tool does not generate, reveal, print or log OTPs,
 * TOTP secrets, provisioning URIs, QR data, recovery-code plaintext, reset
 * tokens, SMTP passwords or payment data.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$checks = [
    'Zoosper\\TwoFactor\\Service\\AdminTwoFactorLoginRedirectService',
    'Zoosper\\TwoFactor\\Service\\AdminTwoFactorEnrollmentService',
    'Zoosper\\TwoFactor\\Repository\\AdminTwoFactorEnrollmentRepository',
    'Zoosper\\TwoFactor\\Controller\\AdminTwoFactorSetupController',
    'Zoosper\\Mail\\Log\\EmailLogRepository',
];

print "Zoosper 2FA login redirect verification\n";
print "=======================================\n\n";

$failed = false;
foreach ($checks as $class) {
    $exists = class_exists($class);
    print '- ' . $class . ': ' . ($exists ? 'ok' : 'missing') . PHP_EOL;
    $failed = $failed || !$exists;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
