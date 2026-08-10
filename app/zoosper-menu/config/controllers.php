<?php
declare(strict_types=1);
use Zoosper\Core\Container\ServiceContainer; use Zoosper\Auth\Service\SessionGuard; use Zoosper\Menu\Admin\Controller\MenuAdminController; use Zoosper\Menu\Admin\MenuAdminResponder; use Zoosper\Menu\Application\MenuAdminService; use Zoosper\Menu\Contract\MenuAdminRepositoryInterface; use Zoosper\Menu\Api\MenuController; use Zoosper\Menu\Contract\MenuProviderInterface;
return [MenuAdminController::class=>static fn(ServiceContainer $s)=>new MenuAdminController($s->get(SessionGuard::class),$s->get(MenuAdminResponder::class),$s->get(MenuAdminService::class),$s->get(MenuAdminRepositoryInterface::class)),MenuController::class=>static fn(ServiceContainer $s)=>new MenuController($s->get(MenuProviderInterface::class))];
