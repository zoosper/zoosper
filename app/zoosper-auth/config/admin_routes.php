<?php

declare(strict_types=1);

use Zoosper\Admin\Controller\RoleAdminController;
use Zoosper\Admin\Controller\UserAdminController;

return [
    ['method' => 'GET', 'path' => '/admin/users', 'controller' => UserAdminController::class, 'action' => 'index', 'permission' => ['role.manage', 'user.manage']],
    ['method' => 'GET', 'path' => '/admin/users/create', 'controller' => UserAdminController::class, 'action' => 'createForm', 'permission' => ['role.manage', 'user.manage']],
    ['method' => 'POST', 'path' => '/admin/users/create', 'controller' => UserAdminController::class, 'action' => 'create', 'permission' => ['role.manage', 'user.manage']],
    ['method' => 'GET', 'path' => '/admin/users/edit', 'controller' => UserAdminController::class, 'action' => 'editForm', 'permission' => ['role.manage', 'user.manage']],
    ['method' => 'POST', 'path' => '/admin/users/edit', 'controller' => UserAdminController::class, 'action' => 'update', 'permission' => ['role.manage', 'user.manage']],

    ['method' => 'GET', 'path' => '/admin/roles', 'controller' => RoleAdminController::class, 'action' => 'index', 'permission' => 'role.manage'],
    ['method' => 'GET', 'path' => '/admin/roles/create', 'controller' => RoleAdminController::class, 'action' => 'createForm', 'permission' => 'role.manage'],
    ['method' => 'POST', 'path' => '/admin/roles/create', 'controller' => RoleAdminController::class, 'action' => 'create', 'permission' => 'role.manage'],
    ['method' => 'GET', 'path' => '/admin/roles/edit', 'controller' => RoleAdminController::class, 'action' => 'editForm', 'permission' => 'role.manage'],
    ['method' => 'POST', 'path' => '/admin/roles/edit', 'controller' => RoleAdminController::class, 'action' => 'update', 'permission' => 'role.manage'],
];
