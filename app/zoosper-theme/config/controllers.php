<?php

declare(strict_types=1);

use Zoosper\Admin\Audit\AuditLogger;
use Zoosper\Admin\Controller\ThemeAdminController;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Site\Repository\SiteRepository;
use Zoosper\Theme\Theme\ThemeRepository;

use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Theme\Api\ThemeApiController;
use Zoosper\Theme\Application\ThemeAssignmentService;
return [
    ThemeApiController::class => static fn (ServiceContainer $s): ThemeApiController => new ThemeApiController($s->get(JsonResponder::class),$s->get(PersonalAccessTokenAuthenticator::class),$s->get(ThemeRepository::class),$s->get(ThemeAssignmentService::class)),

    ThemeAdminController::class => static fn (ServiceContainer $services): ThemeAdminController => new ThemeAdminController(
        $services->get(SessionGuard::class),
        $services->get(CsrfTokenManager::class),
        $services->get(AdminLayout::class),
        $services->get(ThemeRepository::class),
        $services->get(SiteRepository::class),
        $services->has(AuditLogger::class) ? $services->get(AuditLogger::class) : null,
        $services->has(AdminViewRenderer::class) ? $services->get(AdminViewRenderer::class) : null,
        $services->get(AdminUrlGenerator::class),
    ),
];
