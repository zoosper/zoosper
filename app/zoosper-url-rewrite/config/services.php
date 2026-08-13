<?php
declare(strict_types=1);
use Zoosper\Core\Container\ServiceContainer;use Zoosper\UrlRewrite\Repository\UrlRewriteRepository;use Zoosper\UrlRewrite\Service\{RedirectChainInspector,UrlRewriteResolver};
return [UrlRewriteRepository::class=>static fn(ServiceContainer $s):UrlRewriteRepository=>new UrlRewriteRepository($s->get(PDO::class)),UrlRewriteResolver::class=>static fn(ServiceContainer $s):UrlRewriteResolver=>new UrlRewriteResolver($s->get(UrlRewriteRepository::class)),RedirectChainInspector::class=>static fn(ServiceContainer $s):RedirectChainInspector=>new RedirectChainInspector($s->get(UrlRewriteRepository::class))];
