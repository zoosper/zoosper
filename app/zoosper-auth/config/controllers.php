<?php

declare(strict_types=1);

use Zoosper\Admin\Audit\AuditLogger;
use Zoosper\Admin\Controller\RoleAdminController;
use Zoosper\Admin\Controller\UserAdminController;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Repository\RoleRepository;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Container\ServiceContainer;

return [
    UserAdminController::class => static fn (ServiceContainer $services): UserAdminController => new UserAdminController(
        $services->get(SessionGuard::class),
        $services->get(CsrfTokenManager::class),
        $services->get(AdminUserRepository::class),
        $services->get(RoleRepository::class),
        $services->get(PasswordHasher::class),
        $services->get(AdminLayout::class),
    ),

    RoleAdminController::class => static fn (ServiceContainer $services): RoleAdminController => new RoleAdminController(
        $services->get(SessionGuard::class),
        $services->get(CsrfTokenManager::class),
        $services->get(RoleRepository::class),
        $services->get(AdminLayout::class),
        $services->get(AdminUserRepository::class),
        $services->has(AuditLogger::class) ? $services->get(AuditLogger::class) : null,
    ),
];
