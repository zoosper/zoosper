<?php

declare(strict_types=1);

use Zoosper\Admin\Controller\DashboardController;
use Zoosper\Admin\Controller\LoginController;

return [
    ['method' => 'GET', 'path' => '/admin/login', 'controller' => LoginController::class, 'action' => 'show', 'public' => true],
    ['method' => 'POST', 'path' => '/admin/login', 'controller' => LoginController::class, 'action' => 'login', 'public' => true],
    ['method' => 'POST', 'path' => '/admin/logout', 'controller' => LoginController::class, 'action' => 'logout', 'permission' => 'admin.access'],
    ['method' => 'GET', 'path' => '/admin', 'controller' => DashboardController::class, 'action' => 'index', 'permission' => 'admin.access'],
    ['method' => 'POST', 'path' => '/admin/dashboard/preferences', 'controller' => DashboardController::class, 'action' => 'savePreferences', 'permission' => 'admin.access'],
    ['method' => 'POST', 'path' => '/admin/dashboard/preferences/reset', 'controller' => DashboardController::class, 'action' => 'resetPreferences', 'permission' => 'admin.access'],
    ['method' => 'GET', 'path' => '/admin/dashboard/role-defaults', 'controller' => DashboardController::class, 'action' => 'roleDefaults', 'permission' => 'role.manage'],
    ['method' => 'POST', 'path' => '/admin/dashboard/role-defaults', 'controller' => DashboardController::class, 'action' => 'saveRoleDefaults', 'permission' => 'role.manage'],
    ['method' => 'POST', 'path' => '/admin/dashboard/role-defaults/reset', 'controller' => DashboardController::class, 'action' => 'resetRoleDefaults', 'permission' => 'role.manage'],
];










