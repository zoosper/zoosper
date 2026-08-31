<?php

declare(strict_types=1);

use Zoosper\Audit\Controller\AuditLogController;
use Zoosper\Audit\Controller\LoginHistoryController;

return [
    ['method' => 'GET', 'path' => '/admin/audit-log', 'controller' => AuditLogController::class, 'action' => 'index', 'permission' => 'role.manage'],
    ['method' => 'GET', 'path' => '/admin/login-history', 'controller' => LoginHistoryController::class, 'action' => 'index', 'permission' => 'role.manage'],
];
