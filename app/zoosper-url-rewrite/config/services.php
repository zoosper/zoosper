<?php
declare(strict_types=1);
use Zoosper\Audit\Contract\AuditLoggerInterface;use Zoosper\Core\Container\ServiceContainer;use Zoosper\UrlRewrite\Application\UrlRewriteMutationService;use Zoosper\UrlRewrite\Lifecycle\UrlRewriteLifecycleCoordinator;use Zoosper\UrlRewrite\Repository\UrlRewriteRepository;use Zoosper\UrlRewrite\Service\{RedirectChainInspector,RedirectPolicy,UrlRewriteResolver};
return [UrlRewriteRepository::class=>static fn(ServiceContainer $s):UrlRewriteRepository=>new UrlRewriteRepository($s->get(PDO::class)),RedirectPolicy::class=>static fn():RedirectPolicy=>new RedirectPolicy(),UrlRewriteResolver::class=>static fn(ServiceContainer $s):UrlRewriteResolver=>new UrlRewriteResolver($s->get(UrlRewriteRepository::class)),RedirectChainInspector::class=>static fn(ServiceContainer $s):RedirectChainInspector=>new RedirectChainInspector($s->get(UrlRewriteRepository::class)),UrlRewriteMutationService::class=>static fn(ServiceContainer $s):UrlRewriteMutationService=>new UrlRewriteMutationService($s->get(UrlRewriteRepository::class),$s->get(RedirectPolicy::class),$s->get(RedirectChainInspector::class),$s->has(AuditLoggerInterface::class)?$s->get(AuditLoggerInterface::class):null),UrlRewriteLifecycleCoordinator::class=>static fn(ServiceContainer $s):UrlRewriteLifecycleCoordinator=>new UrlRewriteLifecycleCoordinator($s->get(UrlRewriteRepository::class),$s->has(AuditLoggerInterface::class)?$s->get(AuditLoggerInterface::class):null)];










