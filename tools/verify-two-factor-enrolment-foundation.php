<?php

declare(strict_types=1);

/**
 * Verify that 2FA enrolment classes and config are autoloadable.
 *
 * This read-only tool does not generate, display or persist TOTP secrets, OTPs,
 * provisioning URIs, recovery-code plaintext, reset tokens or SMTP passwords.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$checks = [
    'Zoosper\\TwoFactor\\Controller\\AdminTwoFactorSetupController',
    'Zoosper\\TwoFactor\\Crypto\\SecretProtector',
    'Zoosper\\TwoFactor\\Recovery\\RecoveryCodeGenerator',
    'Zoosper\\TwoFactor\\Repository\\AdminTwoFactorEnrollmentRepository',
    'Zoosper\\TwoFactor\\Service\\AdminTwoFactorEnrollmentService',
    'Zoosper\\TwoFactor\\Totp\\Base32',
    'Zoosper\\TwoFactor\\Totp\\TotpSecretGenerator',
    'Zoosper\\TwoFactor\\Totp\\TotpVerifier',
];

print "Zoosper 2FA enrolment foundation verification\n";
print "=============================================\n\n";

$failed = false;
foreach ($checks as $class) {
    $exists = class_exists($class);
    print '- ' . $class . ': ' . ($exists ? 'ok' : 'missing') . PHP_EOL;
    if (!$exists) {
        $failed = true;
    }
}

$configFile = $basePath . '/config/two_factor.php';
$routesFile = $basePath . '/app/zoosper-two-factor/config/admin_routes.php';
print '- config/two_factor.php: ' . (is_file($configFile) ? 'ok' : 'missing') . PHP_EOL;
print '- app/zoosper-two-factor/config/admin_routes.php: ' . (is_file($routesFile) ? 'ok' : 'missing') . PHP_EOL;

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
