<?php

declare(strict_types=1);

use Zoosper\Core\Audit\AuditLoggerInterface;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\TwoFactor\Challenge\TwoFactorChallengeRepository;
use Zoosper\TwoFactor\Challenge\TwoFactorChallengeService;
use Zoosper\TwoFactor\Crypto\SecretProtector;
use Zoosper\TwoFactor\Qr\TotpQrCodeSvgRenderer;
use Zoosper\TwoFactor\Recovery\RecoveryCodeGenerator;
use Zoosper\TwoFactor\Repository\AdminRecoveryCodeRepository;
use Zoosper\TwoFactor\Repository\AdminTwoFactorEnrollmentRepository;
use Zoosper\TwoFactor\Repository\AdminTwoFactorResetRepository;
use Zoosper\TwoFactor\Service\AdminTwoFactorEnrollmentService;
use Zoosper\TwoFactor\Service\AdminTwoFactorLoginRedirectService;
use Zoosper\TwoFactor\Service\AdminTwoFactorResetService;
use Zoosper\TwoFactor\Totp\TotpSecretGenerator;
use Zoosper\TwoFactor\Totp\TotpVerifier;

return [
    AdminTwoFactorResetRepository::class => static fn (ServiceContainer $services): AdminTwoFactorResetRepository => new AdminTwoFactorResetRepository($services->get(PDO::class)),

    // Phase 1.41: depends on Zoosper\Core\Audit\AuditLoggerInterface instead
    // of the concrete Zoosper\Admin\Audit\AuditLogger. If no module binds
    // this interface (e.g. Admin is not installed), $services->has(...)
    // correctly returns false and resets simply skip audit logging.
    AdminTwoFactorResetService::class => static fn (ServiceContainer $services): AdminTwoFactorResetService => new AdminTwoFactorResetService(
        $services->get(AdminTwoFactorResetRepository::class),
        $services->has(AuditLoggerInterface::class) ? $services->get(AuditLoggerInterface::class) : null,
    ),
    AdminTwoFactorEnrollmentRepository::class => static fn (ServiceContainer $services): AdminTwoFactorEnrollmentRepository => new AdminTwoFactorEnrollmentRepository($services->get(PDO::class)),
    AdminRecoveryCodeRepository::class => static fn (ServiceContainer $services): AdminRecoveryCodeRepository => new AdminRecoveryCodeRepository($services->get(PDO::class)),
    TwoFactorChallengeRepository::class => static fn (ServiceContainer $services): TwoFactorChallengeRepository => new TwoFactorChallengeRepository($services->get(PDO::class)),
    TotpSecretGenerator::class => static fn (ServiceContainer $services): TotpSecretGenerator => new TotpSecretGenerator(),
    TotpVerifier::class => static function (ServiceContainer $services): TotpVerifier {
        $config = $services->get(ConfigRepository::class)->array('two_factor');
        return new TotpVerifier(
            (int) ($config['period'] ?? 30),
            (int) ($config['digits'] ?? 6),
            (int) ($config['window'] ?? 1),
        );
    },

    // SECURITY FIX (confirmed 2026-07-30, external reviewer pass): this
    // factory previously fell back to an insecure, publicly-visible
    // placeholder literal (visible in this file's git history, deliberately
    // not repeated verbatim here) if config/two_factor.php's
    // 'encryption_key' was empty/missing. This is the SECOND of two copies
    // of the same insecure fallback found in this codebase (the other was
    // in config/two_factor.php itself, already fixed separately) — this is
    // the one that actually matters, since it's the real point where the
    // key is used to construct working crypto (SecretProtector, backed by
    // libsodium secretbox).
    //
    // An earlier attempt at this fix made config/two_factor.php itself
    // throw when no key was present — that broke unrelated tests
    // (database connection tests, module discovery, frontend boot) because
    // ConfigRepository::fromPath() eagerly loads EVERY config file the
    // moment ANY config is requested, so completely unrelated code paths
    // were tripping a 2FA-specific check. The enforcement now lives HERE
    // instead — the one place that genuinely needs a real key to do real
    // encryption — so unrelated config loading is never affected, while
    // any code that actually tries to build a working SecretProtector
    // without a real key still fails loudly, exactly as intended.
    SecretProtector::class => static function (ServiceContainer $services): SecretProtector {
        $config = $services->get(ConfigRepository::class)->array('two_factor');
        $encryptionKey = (string) ($config['encryption_key'] ?? '');

        if ($encryptionKey === '') {
            throw new \RuntimeException(
                'No 2FA encryption key is configured. Set either the TWO_FACTOR_ENCRYPTION_KEY '
                . 'or APP_KEY environment variable to a strong, random secret before using any '
                . '2FA feature. Admin two-factor (TOTP) secrets are encrypted using this key — '
                . 'without a real, unique key, 2FA cannot be considered secure. '
                . 'Generate one with, for example: php -r "echo bin2hex(random_bytes(32));" '
                . 'and set it as TWO_FACTOR_ENCRYPTION_KEY in your .env file.'
            );
        }

        return new SecretProtector($encryptionKey);
    },
    RecoveryCodeGenerator::class => static fn (ServiceContainer $services): RecoveryCodeGenerator => new RecoveryCodeGenerator(),
    AdminTwoFactorEnrollmentService::class => static function (ServiceContainer $services): AdminTwoFactorEnrollmentService {
        $config = $services->get(ConfigRepository::class)->array('two_factor');
        return new AdminTwoFactorEnrollmentService(
            $services->get(AdminTwoFactorEnrollmentRepository::class),
            $services->get(TotpSecretGenerator::class),
            $services->get(TotpVerifier::class),
            $services->get(SecretProtector::class),
            $services->get(RecoveryCodeGenerator::class),
            (string) ($config['issuer'] ?? 'Zoosper'),
            (int) ($config['recovery_codes'] ?? 8),
        );
    },
    TwoFactorChallengeService::class => static function (ServiceContainer $services): TwoFactorChallengeService {
        $config = $services->get(ConfigRepository::class)->array('two_factor');
        $verifier = $services->get(TotpVerifier::class);
        $recovery = $services->get(AdminRecoveryCodeRepository::class);

        return new TwoFactorChallengeService(
            $services->get(TwoFactorChallengeRepository::class),
            static fn (string $secret, string $code): bool => $verifier->verify($secret, $code),
            static fn (int $adminUserId, string $code): bool => $recovery->redeem($adminUserId, $code),
            (int) ($config['challenge_ttl'] ?? 300),
        );
    },
    AdminTwoFactorLoginRedirectService::class => static function (ServiceContainer $services): AdminTwoFactorLoginRedirectService {
        $adminConfig = $services->get(ConfigRepository::class)->array('admin');
        $adminBasePath = (string) ($adminConfig['base_path'] ?? '/admin');
        return new AdminTwoFactorLoginRedirectService(
            $services->get(AdminTwoFactorEnrollmentService::class),
            $adminBasePath,
            $adminBasePath,
        );
    },
    TotpQrCodeSvgRenderer::class => static fn (ServiceContainer $services): TotpQrCodeSvgRenderer => new TotpQrCodeSvgRenderer(),
];
