<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Controller\RoleAdminController;
use Zoosper\Auth\Admin\Controller\UserAdminController;
use Zoosper\Auth\Admin\Controller\PersonalAccessTokenAdminController;

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

    ['method' => 'POST', 'path' => '/admin/users/{id:\d+}/disable', 'controller' => UserAdminController::class, 'action' => 'disable', 'permission' => 'user.manage'],
    ['method' => 'POST', 'path' => '/admin/users/{id:\d+}/restore', 'controller' => UserAdminController::class, 'action' => 'restore', 'permission' => 'user.manage'],
    ['method' => 'POST', 'path' => '/admin/roles/{id:\d+}/delete', 'controller' => RoleAdminController::class, 'action' => 'deletePermanently', 'permission' => 'role.manage'],
    ['method' => 'GET', 'path' => '/admin/access-tokens', 'controller' => PersonalAccessTokenAdminController::class, 'action' => 'index'],
    ['method' => 'POST', 'path' => '/admin/access-tokens/create', 'controller' => PersonalAccessTokenAdminController::class, 'action' => 'create'],
    ['method' => 'POST', 'path' => '/admin/access-tokens/{id:\d+}/revoke', 'controller' => PersonalAccessTokenAdminController::class, 'action' => 'revoke'],
];
