<?php

declare(strict_types=1);

/**
 * Send a safe 2FA setup-required notification email to an admin user.
 *
 * Usage:
 *   php tools/send-2fa-setup-notification.php --admin-user-id=1
 *
 * The email intentionally excludes OTP values, TOTP secrets, provisioning URIs,
 * QR data, recovery-code plaintext, reset tokens, SMTP passwords and payment
 * data. The outbound attempt is recorded in the SMTP email log through
 * LoggedMailer.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$options = getopt('', ['admin-user-id:']);
$adminUserId = (int) ($options['admin-user-id'] ?? 0);

if ($adminUserId <= 0) {
    fwrite(STDERR, "Usage: php tools/send-2fa-setup-notification.php --admin-user-id=<id>\n");
    exit(1);
}

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$pdo = (new \Zoosper\Core\Database\ConnectionFactory($config, $basePath))->create();
$userRepository = new \Zoosper\Auth\Repository\AdminUserRepository($pdo);
$adminUser = $userRepository->findById($adminUserId);

if ($adminUser === null) {
    fwrite(STDERR, "Admin user not found for ID {$adminUserId}.\n");
    exit(2);
}

$smtpConfig = new \Zoosper\Mail\Config\SmtpConfig($config);
$smtpMailer = new \Zoosper\Mail\Transport\SmtpMailer($smtpConfig);
$loggedMailer = new \Zoosper\Mail\Transport\LoggedMailer($smtpMailer, new \Zoosper\Mail\Log\EmailLogRepository($pdo));
$appUrl = (string) ($config->get('app.url', env('APP_URL', '')) ?? '');
$adminConfig = $config->array('admin');
$adminBasePath = (string) ($adminConfig['base_path'] ?? '/admin');

$service = new \Zoosper\TwoFactor\Service\AdminTwoFactorSetupNotificationService(
    $loggedMailer,
    $smtpConfig,
    $appUrl,
    $adminBasePath,
);

$service->sendSetupRequired($adminUser);

print "2FA setup notification accepted by configured SMTP endpoint and logged for {$adminUser->email}.\n";
