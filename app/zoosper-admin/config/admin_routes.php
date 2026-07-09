<?php

declare(strict_types=1);

use Zoosper\Admin\Controller\AuditLogController;
use Zoosper\Admin\Controller\DashboardController;
use Zoosper\Admin\Controller\LoginController;
use Zoosper\Admin\Controller\LoginHistoryController;

return [
    ['method' => 'GET', 'path' => '/admin/login', 'controller' => LoginController::class, 'action' => 'show', 'public' => true],
    ['method' => 'POST', 'path' => '/admin/login', 'controller' => LoginController::class, 'action' => 'login', 'public' => true],
    ['method' => 'POST', 'path' => '/admin/logout', 'controller' => LoginController::class, 'action' => 'logout', 'permission' => 'admin.access'],
    ['method' => 'GET', 'path' => '/admin', 'controller' => DashboardController::class, 'action' => 'index', 'permission' => 'admin.access'],
    ['method' => 'GET', 'path' => '/admin/audit-log', 'controller' => AuditLogController::class, 'action' => 'index', 'permission' => 'role.manage'],
    ['method' => 'GET', 'path' => '/admin/login-history', 'controller' => LoginHistoryController::class, 'action' => 'index', 'permission' => 'role.manage'],
];
