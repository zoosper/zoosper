<?php

declare(strict_types=1);

/**
 * Auth module admin controller registrations.
 *
 * Phase 1.25b: injects the EntitySaveLifecycleRunner into UserAdminController so
 * admin-user saves run through the entity save lifecycle at runtime.
 */

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
use Zoosper\Core\Entity\Save\EntitySaveLifecycleRunner;
use Zoosper\TwoFactor\Service\AdminTwoFactorResetService;

return [
    UserAdminController::class => static fn (ServiceContainer $services): UserAdminController => new UserAdminController(
        $services->get(SessionGuard::class),
        $services->get(CsrfTokenManager::class),
        $services->get(AdminUserRepository::class),
        $services->get(RoleRepository::class),
        $services->get(PasswordHasher::class),
        $services->get(AdminLayout::class),
        $services->has(AdminTwoFactorResetService::class) ? $services->get(AdminTwoFactorResetService::class) : null,
        saveLifecycle: $services->get(EntitySaveLifecycleRunner::class),
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
