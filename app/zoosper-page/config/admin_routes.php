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
    // Canonical parameterised CRUD/preview routes. Legacy query-string routes remain below during compatibility cutover.
    ['method' => 'GET', 'path' => '/admin/pages/{id:\d+}/edit', 'controller' => PageAdminController::class, 'action' => 'editForm', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/{id:\d+}/edit', 'controller' => PageAdminController::class, 'action' => 'update', 'permission' => 'page.manage'],
    ['method' => 'GET', 'path' => '/admin/pages/{id:\d+}/preview', 'controller' => PageAdminController::class, 'action' => 'preview', 'permission' => 'page.manage'],
    ['method' => 'GET', 'path' => '/admin/pages/{id:\d+}/revisions', 'controller' => PageAdminController::class, 'action' => 'revisionHistory', 'permission' => 'page.manage'],
    ['method' => 'GET', 'path' => '/admin/pages/{id:\d+}/revisions/{revisionId:\d+}/preview', 'controller' => PageAdminController::class, 'action' => 'revisionPreview', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/{id:\d+}/revisions/{revisionId:\d+}/restore', 'controller' => PageAdminController::class, 'action' => 'restoreRevision', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/{id:\d+}/publish', 'controller' => PageAdminController::class, 'action' => 'publish', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/{id:\d+}/unpublish', 'controller' => PageAdminController::class, 'action' => 'unpublish', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/{id:\d+}/archive', 'controller' => PageAdminController::class, 'action' => 'archive', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/{id:\d+}/restore', 'controller' => PageAdminController::class, 'action' => 'restore', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/{id:\d+}/delete', 'controller' => PageAdminController::class, 'action' => 'deletePermanently', 'permission' => 'page.manage'],
    ['method' => 'GET', 'path' => '/admin/pages/edit', 'controller' => PageAdminController::class, 'action' => 'editForm', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/edit', 'controller' => PageAdminController::class, 'action' => 'update', 'permission' => 'page.manage'],
    ['method' => 'GET', 'path' => '/admin/pages/preview', 'controller' => PageAdminController::class, 'action' => 'preview', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/publish', 'controller' => PageAdminController::class, 'action' => 'publish', 'permission' => 'page.manage'],
    ['method' => 'POST', 'path' => '/admin/pages/unpublish', 'controller' => PageAdminController::class, 'action' => 'unpublish', 'permission' => 'page.manage'],
];
