<?php

declare(strict_types=1);

use Zoosper\Auth\Console\AdminCreateCommand;
use Zoosper\Auth\Http\AuthenticationMiddleware;
use Zoosper\Auth\Http\CsrfMiddleware;
use Zoosper\Auth\Http\RateLimitReportOnlyAdminMiddleware;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Repository\RoleRepository;
use Zoosper\Auth\RateLimit\AdminAuthenticationRateLimiter;
use Zoosper\Auth\RateLimit\AdminAuthenticationRateLimiterInterface;
use Zoosper\Auth\Service\AuthService;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Url\AdminUrlGenerator;

return [
    // Auth Grid read-side services. Existing manifest entries below retain precedence.
    ...require __DIR__ . '/services_auth_grid.php',

    AdminUserRepository::class => static fn (ServiceContainer $services): AdminUserRepository => new AdminUserRepository($services->get(PDO::class)),
    RoleRepository::class => static fn (ServiceContainer $services): RoleRepository => new RoleRepository($services->get(PDO::class)),
    PasswordHasher::class => static fn (ServiceContainer $services): PasswordHasher => new PasswordHasher(),
    AuthService::class => static fn (ServiceContainer $services): AuthService => new AuthService(
        $services->get(AdminUserRepository::class),
        $services->get(PasswordHasher::class),
    ),
    SessionGuard::class => static fn (ServiceContainer $services): SessionGuard => new SessionGuard(
        $services->get(AdminUserRepository::class),
        max(0, (int) $services->get(ConfigRepository::class)->get('admin.session_idle_timeout', 7200)),
    ),
    CsrfTokenManager::class => static fn (ServiceContainer $services): CsrfTokenManager => new CsrfTokenManager(),
    AuthenticationMiddleware::class => static fn (ServiceContainer $services): AuthenticationMiddleware => new AuthenticationMiddleware(
        $services->get(SessionGuard::class),
        $services->get(AdminUrlGenerator::class)->url('login'),
    ),
    CsrfMiddleware::class => static fn (ServiceContainer $services): CsrfMiddleware => new CsrfMiddleware(
        $services->get(CsrfTokenManager::class),
        $services->get(AdminUrlGenerator::class)->basePath(),
    ),
    AdminAuthenticationRateLimiterInterface::class => static fn (ServiceContainer $services): AdminAuthenticationRateLimiterInterface => new AdminAuthenticationRateLimiter(
        $services->get(PDO::class),
        dirname(__DIR__, 3),
    ),
    RateLimitReportOnlyAdminMiddleware::class => static fn (ServiceContainer $services): RateLimitReportOnlyAdminMiddleware => new RateLimitReportOnlyAdminMiddleware(
        $services->get(PDO::class),
        dirname(__DIR__, 3),
        $services->get(AdminUrlGenerator::class)->url('login'),
    ),
    AdminCreateCommand::class => static fn (ServiceContainer $services): AdminCreateCommand => new AdminCreateCommand(
        $services->get(AdminUserRepository::class),
        $services->get(PasswordHasher::class),
    ),
];
