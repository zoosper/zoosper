<?php

declare(strict_types=1);

/**
 * Verify 2FA setup notification email dependencies without sending email.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$checks = [
    'Zoosper\\TwoFactor\\Service\\AdminTwoFactorSetupNotificationService',
    'Zoosper\\Mail\\Transport\\LoggedMailer',
    'Zoosper\\Mail\\Log\\EmailLogRepository',
    'Zoosper\\Mail\\Config\\SmtpConfig',
    'Zoosper\\Auth\\Repository\\AdminUserRepository',
];

print "Zoosper 2FA setup notification verification\n";
print "===========================================\n\n";

$failed = false;
foreach ($checks as $class) {
    $ok = class_exists($class);
    print '- ' . $class . ': ' . ($ok ? 'ok' : 'missing') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
