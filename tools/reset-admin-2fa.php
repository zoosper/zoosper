<?php

declare(strict_types=1);

/**
 * Emergency CLI reset for an admin user's 2FA state.
 *
 * Usage:
 *   php tools/reset-admin-2fa.php --admin-user-id=1 --performed-by=1 --yes
 *
 * This tool deletes protected 2FA state only. It never reads, prints or logs
 * OTPs, TOTP secrets, recovery-code plaintext, provisioning URIs or QR data.
 * It uses the shared CLI bootstrap so `.env` is loaded consistently.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$options = getopt('', ['admin-user-id:', 'performed-by:', 'yes']);
$targetId = (int) ($options['admin-user-id'] ?? 0);
$actorId = (int) ($options['performed-by'] ?? 0);
$confirmed = array_key_exists('yes', $options);

if ($targetId <= 0 || $actorId <= 0 || !$confirmed) {
    fwrite(STDERR, "Usage: php tools/reset-admin-2fa.php --admin-user-id=<id> --performed-by=<id> --yes\n");
    exit(1);
}

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$pdo = (new \Zoosper\Core\Database\ConnectionFactory($config, $basePath))->create();
$repository = new \Zoosper\TwoFactor\Repository\AdminTwoFactorResetRepository($pdo);
$missing = $repository->missingTables();

if ($missing !== []) {
    fwrite(STDERR, "2FA reset tables are missing: " . implode(', ', $missing) . ". Run php bin/zoosper migrate before using this tool on this database.\n");
    exit(2);
}

$service = new \Zoosper\TwoFactor\Service\AdminTwoFactorResetService($repository, null);
$service->reset($targetId, $actorId);

print "2FA reset completed for admin user ID {$targetId}.\n";
