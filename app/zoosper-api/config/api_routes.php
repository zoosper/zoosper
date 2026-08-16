<?php

declare(strict_types=1);

use Zoosper\Api\Controller\AuthController;
use Zoosper\Api\Controller\ContentPageController;
use Zoosper\Api\Controller\HealthController;
use Zoosper\Api\Controller\HelloController;
use Zoosper\Api\Controller\MeController;

return [
    ['method' => 'GET', 'path' => '/api/v1/health', 'controller' => HealthController::class, 'action' => 'show', 'public' => true, 'stateless' => true],
    ['method' => 'GET', 'path' => '/api/v1/hello', 'controller' => HelloController::class, 'action' => 'show', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/auth/login', 'controller' => AuthController::class, 'action' => 'login', 'public' => true],
    ['method' => 'POST', 'path' => '/api/v1/auth/logout', 'controller' => AuthController::class, 'action' => 'logout'],
    ['method' => 'GET', 'path' => '/api/v1/me', 'controller' => MeController::class, 'action' => 'show'],
    ['method' => 'GET', 'path' => '/api/v1/token/me', 'controller' => \Zoosper\Api\Controller\TokenMeController::class, 'action' => 'show', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/pages', 'controller' => \Zoosper\Api\Controller\PageApiController::class, 'action' => 'create', 'public' => true, 'stateless' => true],
    ['method' => 'PATCH', 'path' => '/api/v1/pages/{id:\d+}', 'controller' => \Zoosper\Api\Controller\PageApiController::class, 'action' => 'update', 'public' => true, 'stateless' => true],
    ['method' => 'GET', 'path' => '/api/v1/pages', 'controller' => \Zoosper\Api\Controller\PageApiController::class, 'action' => 'index', 'public' => true, 'stateless' => true],
    ['method' => 'GET', 'path' => '/api/v1/pages/{id:\d+}', 'controller' => \Zoosper\Api\Controller\PageApiController::class, 'action' => 'show', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/pages/{id:\d+}/publish', 'controller' => \Zoosper\Api\Controller\PageApiController::class, 'action' => 'publish', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/pages/{id:\d+}/unpublish', 'controller' => \Zoosper\Api\Controller\PageApiController::class, 'action' => 'unpublish', 'public' => true, 'stateless' => true],
    ['method' => 'GET', 'path' => '/api/v1/pages/{id:\d+}/revisions', 'controller' => \Zoosper\Api\Controller\PageApiController::class, 'action' => 'revisions', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/pages/{id:\d+}/revisions/{revisionId:\d+}/restore', 'controller' => \Zoosper\Api\Controller\PageApiController::class, 'action' => 'restoreRevision', 'public' => true, 'stateless' => true],
    ['method' => 'GET', 'path' => '/api/v1/menus', 'controller' => \Zoosper\Api\Controller\MenuApiController::class, 'action' => 'index', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/menus', 'controller' => \Zoosper\Api\Controller\MenuApiController::class, 'action' => 'create', 'public' => true, 'stateless' => true],
    ['method' => 'PATCH', 'path' => '/api/v1/menus/{id:\d+}', 'controller' => \Zoosper\Api\Controller\MenuApiController::class, 'action' => 'update', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/menus/{id:\d+}/items', 'controller' => \Zoosper\Api\Controller\MenuApiController::class, 'action' => 'createItem', 'public' => true, 'stateless' => true],
    ['method' => 'PATCH', 'path' => '/api/v1/menus/{id:\d+}/items/{itemId:\d+}', 'controller' => \Zoosper\Api\Controller\MenuApiController::class, 'action' => 'updateItem', 'public' => true, 'stateless' => true],
    ['method' => 'DELETE', 'path' => '/api/v1/menus/{id:\d+}/items/{itemId:\d+}', 'controller' => \Zoosper\Api\Controller\MenuApiController::class, 'action' => 'deleteItem', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/menus/{id:\d+}/disable', 'controller' => \Zoosper\Api\Controller\MenuApiController::class, 'action' => 'disable', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/menus/{id:\d+}/restore', 'controller' => \Zoosper\Api\Controller\MenuApiController::class, 'action' => 'restore', 'public' => true, 'stateless' => true],
    ['method' => 'DELETE', 'path' => '/api/v1/menus/{id:\d+}', 'controller' => \Zoosper\Api\Controller\MenuApiController::class, 'action' => 'deletePermanently', 'public' => true, 'stateless' => true],
    ['method' => 'GET', 'path' => '/api/v1/menus/{id:\d+}', 'controller' => \Zoosper\Api\Controller\MenuApiController::class, 'action' => 'show', 'public' => true, 'stateless' => true],
    ['method' => 'GET', 'path' => '/api/v1/menus/{id:\d+}/tree', 'controller' => \Zoosper\Api\Controller\MenuApiController::class, 'action' => 'tree', 'public' => true, 'stateless' => true],
    ['method' => 'GET', 'path' => '/api/v1/content/page', 'controller' => ContentPageController::class, 'action' => 'show', 'public' => true, 'stateless' => true],
];
