<?php

declare(strict_types=1);

return array (
  0 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/sites',
    'controller' => 'Zoosper\\Site\\Api\\SiteApiController',
    'action' => 'create',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'sites:write',
    'permission' =>
    array (
      0 => 'settings.manage',
    ),
  ),
  1 =>
  array (
    'method' => 'PATCH',
    'path' => '/api/v1/sites/{id:\\d+}',
    'controller' => 'Zoosper\\Site\\Api\\SiteApiController',
    'action' => 'update',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'sites:write',
    'permission' =>
    array (
      0 => 'settings.manage',
    ),
  ),
  2 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/sites',
    'controller' => 'Zoosper\\Site\\Api\\SiteApiController',
    'action' => 'index',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'sites:read',
    'permission' =>
    array (
      0 => 'settings.manage',
    ),
  ),
  3 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/sites/{id:\\d+}',
    'controller' => 'Zoosper\\Site\\Api\\SiteApiController',
    'action' => 'show',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'sites:read',
    'permission' =>
    array (
      0 => 'settings.manage',
    ),
  ),
  4 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/sites/{id:\\d+}/disable',
    'controller' => 'Zoosper\\Site\\Api\\SiteApiController',
    'action' => 'disable',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'sites:write',
    'permission' =>
    array (
      0 => 'settings.manage',
    ),
  ),
  5 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/sites/{id:\\d+}/restore',
    'controller' => 'Zoosper\\Site\\Api\\SiteApiController',
    'action' => 'restore',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'sites:write',
    'permission' =>
    array (
      0 => 'settings.manage',
    ),
  ),
  6 =>
  array (
    'method' => 'DELETE',
    'path' => '/api/v1/sites/{id:\\d+}',
    'controller' => 'Zoosper\\Site\\Api\\SiteApiController',
    'action' => 'deletePermanently',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'sites:write',
    'permission' =>
    array (
      0 => 'settings.manage',
    ),
  ),
);
