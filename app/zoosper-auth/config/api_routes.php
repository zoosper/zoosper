<?php

declare(strict_types=1);

use Zoosper\Auth\Api\RoleApiController;

return [
    ['method' => 'GET', 'path' => '/api/v1/roles', 'controller' => RoleApiController::class, 'action' => 'index', 'public' => true, 'stateless' => true],
    ['method' => 'GET', 'path' => '/api/v1/roles/{id:\d+}', 'controller' => RoleApiController::class, 'action' => 'show', 'public' => true, 'stateless' => true],
    ['method' => 'GET', 'path' => '/api/v1/permissions', 'controller' => RoleApiController::class, 'action' => 'permissions', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/roles', 'controller' => RoleApiController::class, 'action' => 'create', 'public' => true, 'stateless' => true],
    ['method' => 'PATCH', 'path' => '/api/v1/roles/{id:\d+}', 'controller' => RoleApiController::class, 'action' => 'update', 'public' => true, 'stateless' => true],
    ['method' => 'DELETE', 'path' => '/api/v1/roles/{id:\d+}', 'controller' => RoleApiController::class, 'action' => 'delete', 'public' => true, 'stateless' => true],
];
