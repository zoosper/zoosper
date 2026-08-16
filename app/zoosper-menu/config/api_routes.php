<?php
declare(strict_types=1);
use Zoosper\Menu\Api\MenuController;
use Zoosper\Menu\Api\MenuApiController;
return [
    ['method' => 'GET', 'path' => '/api/v1/menus', 'controller' => MenuApiController::class, 'action' => 'index', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/menus', 'controller' => MenuApiController::class, 'action' => 'create', 'public' => true, 'stateless' => true],
    ['method' => 'PATCH', 'path' => '/api/v1/menus/{id:\d+}', 'controller' => MenuApiController::class, 'action' => 'update', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/menus/{id:\d+}/items', 'controller' => MenuApiController::class, 'action' => 'createItem', 'public' => true, 'stateless' => true],
    ['method' => 'PATCH', 'path' => '/api/v1/menus/{id:\d+}/items/{itemId:\d+}', 'controller' => MenuApiController::class, 'action' => 'updateItem', 'public' => true, 'stateless' => true],
    ['method' => 'DELETE', 'path' => '/api/v1/menus/{id:\d+}/items/{itemId:\d+}', 'controller' => MenuApiController::class, 'action' => 'deleteItem', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/menus/{id:\d+}/disable', 'controller' => MenuApiController::class, 'action' => 'disable', 'public' => true, 'stateless' => true],
    ['method' => 'POST', 'path' => '/api/v1/menus/{id:\d+}/restore', 'controller' => MenuApiController::class, 'action' => 'restore', 'public' => true, 'stateless' => true],
    ['method' => 'DELETE', 'path' => '/api/v1/menus/{id:\d+}', 'controller' => MenuApiController::class, 'action' => 'deletePermanently', 'public' => true, 'stateless' => true],
    ['method' => 'GET', 'path' => '/api/v1/menus/{id:\d+}', 'controller' => MenuApiController::class, 'action' => 'show', 'public' => true, 'stateless' => true],
    ['method' => 'GET', 'path' => '/api/v1/menus/{id:\d+}/tree', 'controller' => MenuApiController::class, 'action' => 'tree', 'public' => true, 'stateless' => true],['method'=>'GET','path'=>'/api/v1/menu','controller'=>MenuController::class,'action'=>'show','public'=>true,'stateless'=>true]];
