<?php

declare(strict_types=1);

/**
 * Auth module admin controller registrations.
 *
 * Phase 1.26a: injects AdminViewRenderer into UserAdminController so it renders
 * Latte templates (zoosper-auth::admin/users/*) instead of inline HTML. The
 * EntitySaveLifecycleRunner injection from Phase 1.25b is preserved.
 *
 * Phase E1: RoleAdminController receives ConfigRepository as its 7th
 * constructor argument, completing Phase 1.111 (Sonnet Phase 2 §3.3).
 *
 * Phase F1: RoleAdminController/UserAdminController relocated from
 * Zoosper\Admin\Controller to Zoosper\Auth\Admin\Controller (use statements
 * updated below; no other change in this file).
 */

use Zoosper\Admin\Audit\AuditLogger;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Admin\Controller\RoleAdminController;
use Zoosper\Auth\Admin\Controller\UserAdminController;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Repository\RoleRepository;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Config\ConfigRepository;
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
        $services->get(AdminViewRenderer::class),
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
        $services->get(ConfigRepository::class),
    ),
];
