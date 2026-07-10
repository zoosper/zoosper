<?php

declare(strict_types=1);

use Zoosper\Mail\Controller\EmailLogAdminController;

return [
    ['method' => 'GET', 'path' => '/admin/mail-logs', 'controller' => EmailLogAdminController::class, 'action' => 'index', 'permission' => 'role.manage'],
    ['method' => 'GET', 'path' => '/admin/mail-logs/view', 'controller' => EmailLogAdminController::class, 'action' => 'view', 'permission' => 'role.manage'],
];
