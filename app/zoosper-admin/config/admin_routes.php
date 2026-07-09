<?php

declare(strict_types=1);

use Zoosper\Admin\Controller\DashboardController;
use Zoosper\Admin\Controller\LoginController;

return [
    ['method' => 'GET', 'path' => '/admin/login', 'controller' => LoginController::class, 'action' => 'show', 'public' => true],
    ['method' => 'POST', 'path' => '/admin/login', 'controller' => LoginController::class, 'action' => 'login', 'public' => true],
    ['method' => 'POST', 'path' => '/admin/logout', 'controller' => LoginController::class, 'action' => 'logout', 'permission' => 'admin.access'],
    ['method' => 'GET', 'path' => '/admin', 'controller' => DashboardController::class, 'action' => 'index', 'permission' => 'admin.access'],
];
