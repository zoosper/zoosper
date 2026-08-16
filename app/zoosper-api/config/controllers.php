<?php

declare(strict_types=1);

use Zoosper\Api\Controller\AuthController as ApiAuthController;
use Zoosper\Api\Controller\ContentPageController;
use Zoosper\Api\Controller\HealthController;
use Zoosper\Api\Controller\HelloController;
use Zoosper\Api\Controller\MeController;
use Zoosper\Api\Controller\TokenMeController;
use Zoosper\Api\Controller\PageApiController;
use Zoosper\Api\Controller\MenuApiController;
use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;
use Zoosper\Auth\Contract\SecondFactorRequirementInterface;
use Zoosper\Auth\RateLimit\AdminAuthenticationRateLimiterInterface;
use Zoosper\Auth\Service\AuthService;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Application\Save\PageSaveCoordinator;
use Zoosper\Page\Application\Publication\PagePublicationCoordinator;
use Zoosper\Page\Service\PageRevisionService;
use Zoosper\Page\Content\BlockJsonToHtmlRenderer;
use Zoosper\Core\Audit\AuditLoggerInterface;
use Zoosper\Site\Repository\SiteRepository;
use Zoosper\Menu\Contract\MenuAdminRepositoryInterface;
use Zoosper\Menu\Contract\MenuProviderInterface;

return [
    ApiAuthController::class => static fn (ServiceContainer $services): ApiAuthController => new ApiAuthController(
        $services->get(JsonResponder::class),
        $services->get(AuthService::class),
        $services->get(SessionGuard::class),
        $services->get(SecondFactorRequirementInterface::class),
        $services->get(AdminAuthenticationRateLimiterInterface::class),
    ),

    HealthController::class => static fn (ServiceContainer $services): HealthController => new HealthController(
        $services->get(JsonResponder::class),
    ),

    HelloController::class => static fn (ServiceContainer $services): HelloController => new HelloController(
        $services->get(JsonResponder::class),
    ),

    MeController::class => static fn (ServiceContainer $services): MeController => new MeController(
        $services->get(JsonResponder::class),
        $services->get(SessionGuard::class),
    ),

    ContentPageController::class => static fn (ServiceContainer $services): ContentPageController => new ContentPageController(
        $services->get(JsonResponder::class),
        $services->get(SiteRepository::class),
        $services->get(PageRepository::class),
    ),
    TokenMeController::class => static fn (ServiceContainer $services): TokenMeController => new TokenMeController($services->get(JsonResponder::class), $services->get(PersonalAccessTokenAuthenticator::class)),
    MenuApiController::class => static fn (ServiceContainer $services): MenuApiController => new MenuApiController(
        $services->get(JsonResponder::class),
        $services->get(PersonalAccessTokenAuthenticator::class),
        $services->get(MenuAdminRepositoryInterface::class),
        $services->get(MenuProviderInterface::class),
    ),
    PageApiController::class => static fn (ServiceContainer $services): PageApiController => new PageApiController(
        $services->get(JsonResponder::class),
        $services->get(PersonalAccessTokenAuthenticator::class),
        $services->get(PageRepository::class),
        $services->get(PageSaveCoordinator::class),
        $services->get(BlockJsonToHtmlRenderer::class),
        $services->get(PagePublicationCoordinator::class),
        $services->get(PageRevisionService::class),
        $services->has(AuditLoggerInterface::class) ? $services->get(AuditLoggerInterface::class) : null,
    ),
];
