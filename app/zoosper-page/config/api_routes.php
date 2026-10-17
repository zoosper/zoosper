<?php

declare(strict_types=1);

return array (
  0 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/pages',
    'controller' => 'Zoosper\\Page\\Api\\PageApiController',
    'action' => 'create',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'pages:write',
    'permission' =>
    array (
      0 => 'page.manage',
    ),
  ),
  1 =>
  array (
    'method' => 'PATCH',
    'path' => '/api/v1/pages/{id:\\d+}',
    'controller' => 'Zoosper\\Page\\Api\\PageApiController',
    'action' => 'update',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'pages:write',
    'permission' =>
    array (
      0 => 'page.manage',
    ),
  ),
  2 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/pages',
    'controller' => 'Zoosper\\Page\\Api\\PageApiController',
    'action' => 'index',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'pages:read',
    'permission' =>
    array (
      0 => 'page.view',
      1 => 'page.manage',
    ),
  ),
  3 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/pages/{id:\\d+}',
    'controller' => 'Zoosper\\Page\\Api\\PageApiController',
    'action' => 'show',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'pages:read',
    'permission' =>
    array (
      0 => 'page.view',
      1 => 'page.manage',
    ),
  ),
  4 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/pages/{id:\\d+}/publish',
    'controller' => 'Zoosper\\Page\\Api\\PageApiController',
    'action' => 'publish',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'pages:publish',
    'permission' =>
    array (
      0 => 'page.manage',
    ),
  ),
  5 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/pages/{id:\\d+}/unpublish',
    'controller' => 'Zoosper\\Page\\Api\\PageApiController',
    'action' => 'unpublish',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'pages:publish',
    'permission' =>
    array (
      0 => 'page.manage',
    ),
  ),
  6 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/pages/{id:\\d+}/archive',
    'controller' => 'Zoosper\\Page\\Api\\PageApiController',
    'action' => 'archive',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'pages:archive',
    'permission' =>
    array (
      0 => 'page.manage',
    ),
  ),
  7 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/pages/{id:\\d+}/restore',
    'controller' => 'Zoosper\\Page\\Api\\PageApiController',
    'action' => 'restoreArchived',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'pages:archive',
    'permission' =>
    array (
      0 => 'page.manage',
    ),
  ),
  8 =>
  array (
    'method' => 'DELETE',
    'path' => '/api/v1/pages/{id:\\d+}',
    'controller' => 'Zoosper\\Page\\Api\\PageApiController',
    'action' => 'deletePermanently',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'pages:delete',
    'permission' =>
    array (
      0 => 'page.manage',
    ),
  ),
  9 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/pages/{id:\\d+}/revisions/{revisionId:\\d+}',
    'controller' => 'Zoosper\\Page\\Api\\PageApiController',
    'action' => 'revision',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'pages:read',
    'permission' =>
    array (
      0 => 'page.view',
      1 => 'page.manage',
    ),
  ),
  10 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/pages/{id:\\d+}/revisions',
    'controller' => 'Zoosper\\Page\\Api\\PageApiController',
    'action' => 'revisions',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'pages:read',
    'permission' =>
    array (
      0 => 'page.view',
      1 => 'page.manage',
    ),
  ),
  11 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/pages/{id:\\d+}/revisions/{revisionId:\\d+}/restore',
    'controller' => 'Zoosper\\Page\\Api\\PageApiController',
    'action' => 'restoreRevision',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'pages:write',
    'permission' =>
    array (
      0 => 'page.manage',
    ),
  ),
  12 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/content/page',
    'controller' => 'Zoosper\\Page\\Api\\ContentPageController',
    'action' => 'show',
    'public' => true,
    'stateless' => true,
    'auth' => 'anonymous',
  ),
);
