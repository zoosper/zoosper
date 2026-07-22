<?php

declare(strict_types=1);

use Zoosper\Site\Admin\Controller\SiteAdminController;
use Zoosper\Site\Admin\Controller\SiteDomainAdminController;

return [
    ['method' => 'GET', 'path' => '/admin/sites', 'controller' => SiteAdminController::class, 'action' => 'index', 'permission' => 'settings.manage'],
    ['method' => 'GET', 'path' => '/admin/sites/create', 'controller' => SiteAdminController::class, 'action' => 'create', 'permission' => 'settings.manage'],
    ['method' => 'POST', 'path' => '/admin/sites/create', 'controller' => SiteAdminController::class, 'action' => 'store', 'permission' => 'settings.manage'],
    ['method' => 'GET', 'path' => '/admin/sites/edit', 'controller' => SiteAdminController::class, 'action' => 'edit', 'permission' => 'settings.manage'],
    ['method' => 'POST', 'path' => '/admin/sites/edit', 'controller' => SiteAdminController::class, 'action' => 'update', 'permission' => 'settings.manage'],
    ['method' => 'GET', 'path' => '/admin/site-domains', 'controller' => SiteDomainAdminController::class, 'action' => 'index', 'permission' => 'settings.manage'],
    ['method' => 'GET', 'path' => '/admin/site-domains/create', 'controller' => SiteDomainAdminController::class, 'action' => 'create', 'permission' => 'settings.manage'],
    ['method' => 'POST', 'path' => '/admin/site-domains/create', 'controller' => SiteDomainAdminController::class, 'action' => 'store', 'permission' => 'settings.manage'],
    ['method' => 'GET', 'path' => '/admin/site-domains/edit', 'controller' => SiteDomainAdminController::class, 'action' => 'edit', 'permission' => 'settings.manage'],
    ['method' => 'POST', 'path' => '/admin/site-domains/edit', 'controller' => SiteDomainAdminController::class, 'action' => 'update', 'permission' => 'settings.manage'],
];
