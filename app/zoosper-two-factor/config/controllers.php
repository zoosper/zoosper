<?php

declare(strict_types=1);

use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\RateLimit\AdminAuthenticationRateLimiterInterface;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\TwoFactor\Challenge\TwoFactorChallengeService;
use Zoosper\TwoFactor\Controller\AdminTwoFactorChallengeController;
use Zoosper\TwoFactor\Controller\AdminTwoFactorSetupController;
use Zoosper\TwoFactor\Qr\TotpQrCodeSvgRenderer;
use Zoosper\TwoFactor\Service\AdminTwoFactorEnrollmentService;

return [
    AdminTwoFactorSetupController::class => static function (ServiceContainer $services): AdminTwoFactorSetupController {
        $adminBasePath = $services->get(AdminUrlGenerator::class)->basePath();

        return new AdminTwoFactorSetupController(
            $services->get(SessionGuard::class),
            $services->get(CsrfTokenManager::class),
            $services->get(AdminLayout::class),
            $services->get(AdminTwoFactorEnrollmentService::class),
            $services->get(TotpQrCodeSvgRenderer::class),
            $adminBasePath,
        );
    },

    AdminTwoFactorChallengeController::class => static function (ServiceContainer $services): AdminTwoFactorChallengeController {
        $adminBasePath = $services->get(AdminUrlGenerator::class)->basePath();

        return new AdminTwoFactorChallengeController(
            $services->get(SessionGuard::class),
            $services->get(CsrfTokenManager::class),
            $services->get(TwoFactorChallengeService::class),
            $services->get(AdminTwoFactorEnrollmentService::class),
            $services->get(AdminUserRepository::class),
            $adminBasePath,
            // Phase 1.113: inject LoginHistoryRepository so a 2FA-completed
            // login (and wrong-code attempts) are recorded in login history.
            $services->has(LoginHistoryRepository::class) ? $services->get(LoginHistoryRepository::class) : null,
            $services->has(AdminAuthenticationRateLimiterInterface::class) ? $services->get(AdminAuthenticationRateLimiterInterface::class) : null,
        );
    },
];
