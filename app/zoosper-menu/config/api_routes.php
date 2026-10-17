<?php

declare(strict_types=1);

return array (
  0 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/menus',
    'controller' => 'Zoosper\\Menu\\Api\\MenuApiController',
    'action' => 'index',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'menus:read',
    'permission' =>
    array (
      0 => 'menu.manage',
    ),
  ),
  1 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/menus',
    'controller' => 'Zoosper\\Menu\\Api\\MenuApiController',
    'action' => 'create',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'menus:write',
    'permission' =>
    array (
      0 => 'menu.manage',
    ),
  ),
  2 =>
  array (
    'method' => 'PATCH',
    'path' => '/api/v1/menus/{id:\\d+}',
    'controller' => 'Zoosper\\Menu\\Api\\MenuApiController',
    'action' => 'update',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'menus:write',
    'permission' =>
    array (
      0 => 'menu.manage',
    ),
  ),
  3 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/menus/{id:\\d+}/items',
    'controller' => 'Zoosper\\Menu\\Api\\MenuApiController',
    'action' => 'createItem',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'menus:write',
    'permission' =>
    array (
      0 => 'menu.manage',
    ),
  ),
  4 =>
  array (
    'method' => 'PATCH',
    'path' => '/api/v1/menus/{id:\\d+}/items/{itemId:\\d+}',
    'controller' => 'Zoosper\\Menu\\Api\\MenuApiController',
    'action' => 'updateItem',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'menus:write',
    'permission' =>
    array (
      0 => 'menu.manage',
    ),
  ),
  5 =>
  array (
    'method' => 'DELETE',
    'path' => '/api/v1/menus/{id:\\d+}/items/{itemId:\\d+}',
    'controller' => 'Zoosper\\Menu\\Api\\MenuApiController',
    'action' => 'deleteItem',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'menus:write',
    'permission' =>
    array (
      0 => 'menu.manage',
    ),
  ),
  6 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/menus/{id:\\d+}/disable',
    'controller' => 'Zoosper\\Menu\\Api\\MenuApiController',
    'action' => 'disable',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'menus:write',
    'permission' =>
    array (
      0 => 'menu.manage',
    ),
  ),
  7 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/menus/{id:\\d+}/restore',
    'controller' => 'Zoosper\\Menu\\Api\\MenuApiController',
    'action' => 'restore',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'menus:write',
    'permission' =>
    array (
      0 => 'menu.manage',
    ),
  ),
  8 =>
  array (
    'method' => 'DELETE',
    'path' => '/api/v1/menus/{id:\\d+}',
    'controller' => 'Zoosper\\Menu\\Api\\MenuApiController',
    'action' => 'deletePermanently',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'menus:write',
    'permission' =>
    array (
      0 => 'menu.manage',
    ),
  ),
  9 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/menus/{id:\\d+}',
    'controller' => 'Zoosper\\Menu\\Api\\MenuApiController',
    'action' => 'show',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'menus:read',
    'permission' =>
    array (
      0 => 'menu.manage',
    ),
  ),
  10 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/menus/{id:\\d+}/tree',
    'controller' => 'Zoosper\\Menu\\Api\\MenuApiController',
    'action' => 'tree',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'menus:read',
    'permission' =>
    array (
      0 => 'menu.manage',
    ),
  ),
  11 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/menu',
    'controller' => 'Zoosper\\Menu\\Api\\MenuController',
    'action' => 'show',
    'public' => true,
    'stateless' => true,
    'auth' => 'anonymous',
  ),
);
