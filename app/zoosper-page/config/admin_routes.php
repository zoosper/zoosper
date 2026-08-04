<?php

declare(strict_types=1);

use Zoosper\Page\Admin\Controller\PageAdminController;
use Zoosper\Page\Admin\Controller\PageBulkActionController;
use Zoosper\Page\Admin\Controller\PageCsvExportController;

return [
    ['method' => 'GET', 'path' => '/admin/pages', 'controller' => PageAdminController::class, 'action' => 'index', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/bulk-action', 'controller' => PageBulkActionController::class, 'action' => 'execute', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/grid', 'controller' => PageAdminController::class, 'action' => 'gridMutation', 'permission' => 'page.manage'],
    ['method' => 'GET', 'path' => '/admin/pages/export', 'controller' => PageCsvExportController::class, 'action' => 'export', 'permission' => 'page.manage'],
    ['method' => 'GET', 'path' => '/admin/pages/create', 'controller' => PageAdminController::class, 'action' => 'createForm', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/create', 'controller' => PageAdminController::class, 'action' => 'create', 'permission' => 'page.manage'],
    ['method' => 'GET', 'path' => '/admin/pages/edit', 'controller' => PageAdminController::class, 'action' => 'editForm', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/edit', 'controller' => PageAdminController::class, 'action' => 'update', 'permission' => 'page.manage'],
    ['method' => 'GET', 'path' => '/admin/pages/preview', 'controller' => PageAdminController::class, 'action' => 'preview', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/publish', 'controller' => PageAdminController::class, 'action' => 'publish', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/unpublish', 'controller' => PageAdminController::class, 'action' => 'unpublish', 'permission' => 'page.manage'],
    [
        'name' => 'admin.page_momentum.index',
        'method' => 'GET',
        'path' => '/admin/page-momentum',
        'controller' => 'Zoosper\\Page\\Admin\\Controller\\PageMomentumAdminHttpController',
        'action' => 'index',
        'view' => 'admin/page-momentum.latte',
        'permission' => 'page.manage',
        'description' => 'Read-only launch-readiness panel for page/admin momentum.',
    ],
];
