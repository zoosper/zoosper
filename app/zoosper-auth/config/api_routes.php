<?php

declare(strict_types=1);

return array (
  0 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/roles',
    'controller' => 'Zoosper\\Auth\\Api\\RoleApiController',
    'action' => 'index',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'roles:read',
    'permission' =>
    array (
      0 => 'role.view',
      1 => 'role.manage',
    ),
  ),
  1 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/roles/{id:\\d+}',
    'controller' => 'Zoosper\\Auth\\Api\\RoleApiController',
    'action' => 'show',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'roles:read',
    'permission' =>
    array (
      0 => 'role.view',
      1 => 'role.manage',
    ),
  ),
  2 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/permissions',
    'controller' => 'Zoosper\\Auth\\Api\\RoleApiController',
    'action' => 'permissions',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'roles:read',
    'permission' =>
    array (
      0 => 'role.view',
      1 => 'role.manage',
    ),
  ),
  3 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/roles',
    'controller' => 'Zoosper\\Auth\\Api\\RoleApiController',
    'action' => 'create',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'roles:write',
    'permission' =>
    array (
      0 => 'role.manage',
    ),
  ),
  4 =>
  array (
    'method' => 'PATCH',
    'path' => '/api/v1/roles/{id:\\d+}',
    'controller' => 'Zoosper\\Auth\\Api\\RoleApiController',
    'action' => 'update',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'roles:write',
    'permission' =>
    array (
      0 => 'role.manage',
    ),
  ),
  5 =>
  array (
    'method' => 'DELETE',
    'path' => '/api/v1/roles/{id:\\d+}',
    'controller' => 'Zoosper\\Auth\\Api\\RoleApiController',
    'action' => 'delete',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'roles:write',
    'permission' =>
    array (
      0 => 'role.manage',
    ),
  ),
);
