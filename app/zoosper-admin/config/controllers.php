<?php

declare(strict_types=1);

use Zoosper\Admin\Controller\DashboardController;
use Zoosper\Admin\Controller\LoginController;
use Zoosper\Admin\Dashboard\DashboardPersonalisationService;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\RateLimit\AdminAuthenticationRateLimiterInterface;
use Zoosper\Auth\Service\AuthService;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\TwoFactor\Challenge\TwoFactorChallengeService;
use Zoosper\TwoFactor\Service\AdminTwoFactorEnrollmentService;
use Zoosper\TwoFactor\Service\AdminTwoFactorLoginRedirectService;

return [
    LoginController::class => static fn (ServiceContainer $services): LoginController => new LoginController(
        $services->get(AuthService::class),
        $services->get(SessionGuard::class),
        $services->get(CsrfTokenManager::class),
        $services->get(\Zoosper\Audit\LoginHistoryRepository::class),
        $services->has(AdminTwoFactorLoginRedirectService::class) ? $services->get(AdminTwoFactorLoginRedirectService::class) : null,
        $services->has(AdminTwoFactorEnrollmentService::class) ? $services->get(AdminTwoFactorEnrollmentService::class) : null,
        $services->has(TwoFactorChallengeService::class) ? $services->get(TwoFactorChallengeService::class) : null,
        $services->get(AdminUrlGenerator::class),
        $services->has(AdminAuthenticationRateLimiterInterface::class) ? $services->get(AdminAuthenticationRateLimiterInterface::class) : null,
    ),

    DashboardController::class => static fn (ServiceContainer $services): DashboardController => new DashboardController(
        $services->get(SessionGuard::class),
        $services->get(CsrfTokenManager::class),
        $services->get(AdminLayout::class),
        $services->get(DashboardPersonalisationService::class),
        $services->get(AdminUrlGenerator::class),
        $services->has(FlashMessageStoreInterface::class) ? $services->get(FlashMessageStoreInterface::class) : null,
        $services->has(AdminViewRenderer::class) ? $services->get(AdminViewRenderer::class) : null,
        $services->has(\Zoosper\Audit\Contract\AuditLoggerInterface::class) ? $services->get(\Zoosper\Audit\Contract\AuditLoggerInterface::class) : null,
    ),
];











