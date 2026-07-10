<?php

declare(strict_types=1);

use Zoosper\Admin\Audit\AuditLogRepository;
use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Admin\Controller\AuditLogController;
use Zoosper\Admin\Controller\DashboardController;
use Zoosper\Admin\Controller\LoginController;
use Zoosper\Admin\Controller\LoginHistoryController;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Service\AuthService;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\TwoFactor\Crypto\SecretProtector;
use Zoosper\TwoFactor\Recovery\RecoveryCodeGenerator;
use Zoosper\TwoFactor\Repository\AdminTwoFactorEnrollmentRepository;
use Zoosper\TwoFactor\Service\AdminTwoFactorEnrollmentService;
use Zoosper\TwoFactor\Service\AdminTwoFactorLoginRedirectService;
use Zoosper\TwoFactor\Totp\TotpSecretGenerator;
use Zoosper\TwoFactor\Totp\TotpVerifier;

return [
    LoginController::class => static function (ServiceContainer $services): LoginController {
        $config = $services->get(ConfigRepository::class);
        $twoFactorConfig = $config->array('two_factor');
        $adminConfig = $config->array('admin');
        $period = (int) ($twoFactorConfig['period'] ?? 30);
        $digits = (int) ($twoFactorConfig['digits'] ?? 6);
        $window = (int) ($twoFactorConfig['window'] ?? 1);
        $issuer = (string) ($twoFactorConfig['issuer'] ?? 'Zoosper');
        $key = (string) ($twoFactorConfig['encryption_key'] ?? 'change-me-before-production');
        $recoveryCount = (int) ($twoFactorConfig['recovery_codes'] ?? 8);
        $adminBasePath = (string) ($adminConfig['base_path'] ?? '/admin');

        $enrolment = new AdminTwoFactorEnrollmentService(
            new AdminTwoFactorEnrollmentRepository($services->get(PDO::class)),
            new TotpSecretGenerator(),
            new TotpVerifier($period, $digits, $window),
            new SecretProtector($key),
            new RecoveryCodeGenerator(),
            $issuer,
            $recoveryCount,
        );

        return new LoginController(
            $services->get(AuthService::class),
            $services->get(SessionGuard::class),
            $services->get(CsrfTokenManager::class),
            $services->get(LoginHistoryRepository::class),
            new AdminTwoFactorLoginRedirectService($enrolment, $adminBasePath, $adminBasePath),
        );
    },

    DashboardController::class => static fn (ServiceContainer $services): DashboardController => new DashboardController(
        $services->get(SessionGuard::class),
        $services->get(CsrfTokenManager::class),
        $services->get(AdminLayout::class),
        $services->has(AdminViewRenderer::class) ? $services->get(AdminViewRenderer::class) : null,
    ),

    AuditLogController::class => static fn (ServiceContainer $services): AuditLogController => new AuditLogController(
        $services->get(SessionGuard::class),
        $services->get(AuditLogRepository::class),
        $services->get(AdminLayout::class),
        $services->has(AdminViewRenderer::class) ? $services->get(AdminViewRenderer::class) : null,
    ),

    LoginHistoryController::class => static fn (ServiceContainer $services): LoginHistoryController => new LoginHistoryController(
        $services->get(SessionGuard::class),
        $services->get(LoginHistoryRepository::class),
        $services->get(AdminLayout::class),
        $services->has(AdminViewRenderer::class) ? $services->get(AdminViewRenderer::class) : null,
    ),
];
