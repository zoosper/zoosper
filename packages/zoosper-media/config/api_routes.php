<?php

declare(strict_types=1);

return array (
  0 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/media',
    'controller' => 'Zoosper\\Media\\Api\\MediaApiController',
    'action' => 'index',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'media:read',
    'permission' =>
    array (
      0 => 'media.manage',
    ),
  ),
  1 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/media',
    'controller' => 'Zoosper\\Media\\Api\\MediaApiController',
    'action' => 'upload',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'media:upload',
    'permission' =>
    array (
      0 => 'media.manage',
    ),
  ),
  2 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/media/{id:\\d+}',
    'controller' => 'Zoosper\\Media\\Api\\MediaApiController',
    'action' => 'show',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'media:read',
    'permission' =>
    array (
      0 => 'media.manage',
    ),
  ),
  3 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/media/{id:\\d+}/derivatives',
    'controller' => 'Zoosper\\Media\\Api\\MediaApiController',
    'action' => 'derivatives',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'media:read',
    'permission' =>
    array (
      0 => 'media.manage',
    ),
  ),
  4 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/media/{id:\\d+}/archive',
    'controller' => 'Zoosper\\Media\\Api\\MediaApiController',
    'action' => 'archive',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'media:delete',
    'permission' =>
    array (
      0 => 'media.manage',
    ),
  ),
  5 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/media/{id:\\d+}/restore',
    'controller' => 'Zoosper\\Media\\Api\\MediaApiController',
    'action' => 'restore',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'media:delete',
    'permission' =>
    array (
      0 => 'media.manage',
    ),
  ),
  6 =>
  array (
    'method' => 'DELETE',
    'path' => '/api/v1/media/{id:\\d+}',
    'controller' => 'Zoosper\\Media\\Api\\MediaApiController',
    'action' => 'deletePermanently',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'media:delete',
    'permission' =>
    array (
      0 => 'media.manage',
    ),
  ),
);
