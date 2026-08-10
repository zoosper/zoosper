<?php
declare(strict_types=1);
use Zoosper\Core\Container\ServiceContainer; use Zoosper\Menu\Contract\{BreadcrumbProviderInterface,MenuItemRepositoryInterface,MenuProviderInterface,MenuRepositoryInterface}; use Zoosper\Menu\Repository\{PdoMenuItemRepository,PdoMenuRepository}; use Zoosper\Menu\Service\{BreadcrumbProvider,MenuProvider,MenuTreeBuilder};
return [
 MenuRepositoryInterface::class=>static fn(ServiceContainer $s)=>new PdoMenuRepository($s->get(PDO::class)),
 MenuItemRepositoryInterface::class=>static fn(ServiceContainer $s)=>new PdoMenuItemRepository($s->get(PDO::class)),
 MenuTreeBuilder::class=>static fn()=>new MenuTreeBuilder(),
 MenuProviderInterface::class=>static fn(ServiceContainer $s)=>new MenuProvider($s->get(MenuRepositoryInterface::class),$s->get(MenuItemRepositoryInterface::class),$s->get(MenuTreeBuilder::class),$s->get(PDO::class)),
 BreadcrumbProviderInterface::class=>static fn(ServiceContainer $s)=>new BreadcrumbProvider($s->get(MenuProviderInterface::class)),
];
