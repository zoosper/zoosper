<?php

declare(strict_types=1);

use Zoosper\Admin\Controller\PageAdminController;

return [
    ['method' => 'GET', 'path' => '/admin/pages', 'controller' => PageAdminController::class, 'action' => 'index', 'permission' => 'page.manage'],
    ['method' => 'GET', 'path' => '/admin/pages/create', 'controller' => PageAdminController::class, 'action' => 'createForm', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/create', 'controller' => PageAdminController::class, 'action' => 'create', 'permission' => 'page.manage'],
    ['method' => 'GET', 'path' => '/admin/pages/edit', 'controller' => PageAdminController::class, 'action' => 'editForm', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/edit', 'controller' => PageAdminController::class, 'action' => 'update', 'permission' => 'page.manage'],
    ['method' => 'GET', 'path' => '/admin/pages/preview', 'controller' => PageAdminController::class, 'action' => 'preview', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/publish', 'controller' => PageAdminController::class, 'action' => 'publish', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/unpublish', 'controller' => PageAdminController::class, 'action' => 'unpublish', 'permission' => 'page.manage'],
];
