<?php

declare(strict_types=1);

use Zoosper\Admin\Audit\AuditLogger;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\TwoFactor\Crypto\SecretProtector;
use Zoosper\TwoFactor\Qr\TotpQrCodeSvgRenderer;
use Zoosper\TwoFactor\Recovery\RecoveryCodeGenerator;
use Zoosper\TwoFactor\Repository\AdminTwoFactorEnrollmentRepository;
use Zoosper\TwoFactor\Repository\AdminTwoFactorResetRepository;
use Zoosper\TwoFactor\Service\AdminTwoFactorEnrollmentService;
use Zoosper\TwoFactor\Service\AdminTwoFactorLoginRedirectService;
use Zoosper\TwoFactor\Service\AdminTwoFactorResetService;
use Zoosper\TwoFactor\Totp\TotpSecretGenerator;
use Zoosper\TwoFactor\Totp\TotpVerifier;

return [
    AdminTwoFactorResetRepository::class => static fn (ServiceContainer $services): AdminTwoFactorResetRepository => new AdminTwoFactorResetRepository($services->get(PDO::class)),
    AdminTwoFactorResetService::class => static fn (ServiceContainer $services): AdminTwoFactorResetService => new AdminTwoFactorResetService(
        $services->get(AdminTwoFactorResetRepository::class),
        $services->has(AuditLogger::class) ? $services->get(AuditLogger::class) : null,
    ),
    AdminTwoFactorEnrollmentRepository::class => static fn (ServiceContainer $services): AdminTwoFactorEnrollmentRepository => new AdminTwoFactorEnrollmentRepository($services->get(PDO::class)),
    TotpSecretGenerator::class => static fn (ServiceContainer $services): TotpSecretGenerator => new TotpSecretGenerator(),
    TotpVerifier::class => static function (ServiceContainer $services): TotpVerifier {
        $config = $services->get(ConfigRepository::class)->array('two_factor');
        return new TotpVerifier(
            (int) ($config['period'] ?? 30),
            (int) ($config['digits'] ?? 6),
            (int) ($config['window'] ?? 1),
        );
    },
    SecretProtector::class => static function (ServiceContainer $services): SecretProtector {
        $config = $services->get(ConfigRepository::class)->array('two_factor');
        return new SecretProtector((string) ($config['encryption_key'] ?? 'change-me-before-production'));
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
