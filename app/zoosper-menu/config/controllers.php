<?php
declare(strict_types=1);
use Zoosper\Core\Container\ServiceContainer; use Zoosper\Menu\Api\MenuController; use Zoosper\Menu\Contract\MenuProviderInterface;
return [MenuController::class=>static fn(ServiceContainer $s)=>new MenuController($s->get(MenuProviderInterface::class))];
