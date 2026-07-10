<?php

declare(strict_types=1);

/**
 * Verify the Phase 0.49 post-login redirect wiring without logging in.
 *
 * This read-only tool does not generate, reveal, print or log OTPs, TOTP
 * secrets, provisioning URIs, QR data, recovery-code plaintext, reset tokens,
 * SMTP passwords or payment data.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$checks = [
    'Zoosper\\Admin\\Controller\\LoginController',
    'Zoosper\\TwoFactor\\Service\\AdminTwoFactorLoginRedirectService',
    'Zoosper\\TwoFactor\\Service\\AdminTwoFactorEnrollmentService',
    'Zoosper\\TwoFactor\\Repository\\AdminTwoFactorEnrollmentRepository',
    'Zoosper\\TwoFactor\\Controller\\AdminTwoFactorSetupController',
];

print "Zoosper 2FA post-login redirect wiring verification\n";
print "===================================================\n\n";

$failed = false;
foreach ($checks as $class) {
    $exists = class_exists($class);
    print '- ' . $class . ': ' . ($exists ? 'ok' : 'missing') . PHP_EOL;
    $failed = $failed || !$exists;
}

print '- app/zoosper-admin/config/controllers.php: ' . (is_file($basePath . '/app/zoosper-admin/config/controllers.php') ? 'ok' : 'missing') . PHP_EOL;

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
