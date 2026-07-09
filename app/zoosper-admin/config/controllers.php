<?php

declare(strict_types=1);

use Zoosper\Admin\Audit\AuditLogRepository;
use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Admin\Controller\AuditLogController;
use Zoosper\Admin\Controller\DashboardController;
use Zoosper\Admin\Controller\LoginController;
use Zoosper\Admin\Controller\LoginHistoryController;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Service\AuthService;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Container\ServiceContainer;

return [
    LoginController::class => static fn (ServiceContainer $services): LoginController => new LoginController(
        $services->get(AuthService::class),
        $services->get(SessionGuard::class),
        $services->get(CsrfTokenManager::class),
        $services->get(LoginHistoryRepository::class),
    ),

    DashboardController::class => static fn (ServiceContainer $services): DashboardController => new DashboardController(
        $services->get(SessionGuard::class),
        $services->get(CsrfTokenManager::class),
        $services->get(AdminLayout::class),
    ),

    AuditLogController::class => static fn (ServiceContainer $services): AuditLogController => new AuditLogController(
        $services->get(SessionGuard::class),
        $services->get(AuditLogRepository::class),
        $services->get(AdminLayout::class),
    ),

    LoginHistoryController::class => static fn (ServiceContainer $services): LoginHistoryController => new LoginHistoryController(
        $services->get(SessionGuard::class),
        $services->get(LoginHistoryRepository::class),
        $services->get(AdminLayout::class),
    ),
];
