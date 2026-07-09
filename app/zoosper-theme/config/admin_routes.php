<?php

declare(strict_types=1);

use Zoosper\Admin\Controller\ThemeAdminController;

return [
    ['method' => 'GET', 'path' => '/admin/themes', 'controller' => ThemeAdminController::class, 'action' => 'index', 'permission' => 'settings.manage'],
    ['method' => 'POST', 'path' => '/admin/themes/assign', 'controller' => ThemeAdminController::class, 'action' => 'assign', 'permission' => 'settings.manage'],
];
