<?php

declare(strict_types=1);

use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\UrlRewrite\Api\UrlRewriteApiController;
use Zoosper\UrlRewrite\Repository\UrlRewriteRepository;
use Zoosper\UrlRewrite\Service\RedirectChainInspector;
use Zoosper\UrlRewrite\Service\RedirectPolicy;

return [
    UrlRewriteApiController::class => static fn (ServiceContainer $services): UrlRewriteApiController => new UrlRewriteApiController(
        $services->get(JsonResponder::class),
        $services->get(PersonalAccessTokenAuthenticator::class),
        $services->get(UrlRewriteRepository::class),
        new RedirectPolicy(),
        new RedirectChainInspector($services->get(UrlRewriteRepository::class)),
    ),
];
