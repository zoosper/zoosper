<?php
declare(strict_types=1);
use Zoosper\Core\Container\ServiceContainer;use Zoosper\Core\Routing\FallbackHandlerInterface;use Zoosper\UrlRewrite\Routing\UrlRewriteFallbackHandler;use Zoosper\UrlRewrite\Service\UrlRewriteResolver;
return [FallbackHandlerInterface::class=>static fn(ServiceContainer $s,FallbackHandlerInterface $inner):FallbackHandlerInterface=>new UrlRewriteFallbackHandler($s->get(UrlRewriteResolver::class),$inner)];
