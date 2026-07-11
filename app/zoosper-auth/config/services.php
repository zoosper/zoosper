<?php

declare(strict_types=1);

use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Repository\RoleRepository;
use Zoosper\Auth\Service\AuthService;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Container\ServiceContainer;

return [
    AdminUserRepository::class => static fn (ServiceContainer $services): AdminUserRepository => new AdminUserRepository($services->get(PDO::class)),
    RoleRepository::class => static fn (ServiceContainer $services): RoleRepository => new RoleRepository($services->get(PDO::class)),
    PasswordHasher::class => static fn (ServiceContainer $services): PasswordHasher => new PasswordHasher(),
    AuthService::class => static fn (ServiceContainer $services): AuthService => new AuthService(
        $services->get(AdminUserRepository::class),
        $services->get(PasswordHasher::class),
    ),
    SessionGuard::class => static fn (ServiceContainer $services): SessionGuard => new SessionGuard($services->get(AdminUserRepository::class)),
    CsrfTokenManager::class => static fn (ServiceContainer $services): CsrfTokenManager => new CsrfTokenManager(),
];
