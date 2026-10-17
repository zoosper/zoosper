<?php

declare(strict_types=1);

return array (
  0 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/themes',
    'controller' => 'Zoosper\\Theme\\Api\\ThemeApiController',
    'action' => 'index',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'themes:read',
    'permission' =>
    array (
      0 => 'settings.manage',
    ),
  ),
  1 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/sites/{id:\\d+}/theme',
    'controller' => 'Zoosper\\Theme\\Api\\ThemeApiController',
    'action' => 'assign',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'themes:write',
    'permission' =>
    array (
      0 => 'settings.manage',
    ),
  ),
);
