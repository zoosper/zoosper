<?php
declare(strict_types=1);
use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;use Zoosper\Core\Container\ServiceContainer;use Zoosper\Core\Http\JsonResponder;use Zoosper\UrlRewrite\Api\UrlRewriteApiController;use Zoosper\UrlRewrite\Application\UrlRewriteMutationService;use Zoosper\UrlRewrite\Lifecycle\UrlRewriteLifecycleCoordinator;use Zoosper\UrlRewrite\Repository\UrlRewriteRepository;
return [UrlRewriteApiController::class=>static fn(ServiceContainer $s):UrlRewriteApiController=>new UrlRewriteApiController($s->get(JsonResponder::class),$s->get(PersonalAccessTokenAuthenticator::class),$s->get(UrlRewriteRepository::class),$s->get(UrlRewriteMutationService::class),$s->get(UrlRewriteLifecycleCoordinator::class))];










