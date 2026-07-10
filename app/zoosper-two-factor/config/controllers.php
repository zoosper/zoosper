<?php

declare(strict_types=1);

use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\TwoFactor\Controller\AdminTwoFactorSetupController;
use Zoosper\TwoFactor\Crypto\SecretProtector;
use Zoosper\TwoFactor\Recovery\RecoveryCodeGenerator;
use Zoosper\TwoFactor\Repository\AdminTwoFactorEnrollmentRepository;
use Zoosper\TwoFactor\Service\AdminTwoFactorEnrollmentService;
use Zoosper\TwoFactor\Totp\TotpSecretGenerator;
use Zoosper\TwoFactor\Totp\TotpVerifier;

return [
    AdminTwoFactorSetupController::class => static function (ServiceContainer $services): AdminTwoFactorSetupController {
        $config = $services->get(ConfigRepository::class);
        $twoFactorConfig = $config->array('two_factor');
        $period = (int) ($twoFactorConfig['period'] ?? 30);
        $digits = (int) ($twoFactorConfig['digits'] ?? 6);
        $window = (int) ($twoFactorConfig['window'] ?? 1);
        $issuer = (string) ($twoFactorConfig['issuer'] ?? 'Zoosper');
        $key = (string) ($twoFactorConfig['encryption_key'] ?? 'change-me-before-production');
        $recoveryCount = (int) ($twoFactorConfig['recovery_codes'] ?? 8);

        $enrolment = new AdminTwoFactorEnrollmentService(
            new AdminTwoFactorEnrollmentRepository($services->get(PDO::class)),
            new TotpSecretGenerator(),
            new TotpVerifier($period, $digits, $window),
            new SecretProtector($key),
            new RecoveryCodeGenerator(),
            $issuer,
            $recoveryCount,
        );

        return new AdminTwoFactorSetupController(
            $services->get(SessionGuard::class),
            $services->get(CsrfTokenManager::class),
            $services->get(AdminLayout::class),
            $enrolment,
        );
    },
];
