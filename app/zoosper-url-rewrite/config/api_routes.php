<?php

declare(strict_types=1);

return array (
  0 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/url-rewrites',
    'controller' => 'Zoosper\\UrlRewrite\\Api\\UrlRewriteApiController',
    'action' => 'index',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'url_rewrites:read',
    'permission' =>
    array (
      0 => 'settings.manage',
    ),
  ),
  1 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/url-rewrites/{id:\\d+}',
    'controller' => 'Zoosper\\UrlRewrite\\Api\\UrlRewriteApiController',
    'action' => 'show',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'url_rewrites:read',
    'permission' =>
    array (
      0 => 'settings.manage',
    ),
  ),
  2 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/url-rewrites',
    'controller' => 'Zoosper\\UrlRewrite\\Api\\UrlRewriteApiController',
    'action' => 'create',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'url_rewrites:write',
    'permission' =>
    array (
      0 => 'settings.manage',
    ),
  ),
  3 =>
  array (
    'method' => 'PATCH',
    'path' => '/api/v1/url-rewrites/{id:\\d+}',
    'controller' => 'Zoosper\\UrlRewrite\\Api\\UrlRewriteApiController',
    'action' => 'update',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'url_rewrites:write',
    'permission' =>
    array (
      0 => 'settings.manage',
    ),
  ),
  4 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/url-rewrites/{id:\\d+}/disable',
    'controller' => 'Zoosper\\UrlRewrite\\Api\\UrlRewriteApiController',
    'action' => 'disable',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'url_rewrites:write',
    'permission' =>
    array (
      0 => 'settings.manage',
    ),
  ),
  5 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/url-rewrites/{id:\\d+}/restore',
    'controller' => 'Zoosper\\UrlRewrite\\Api\\UrlRewriteApiController',
    'action' => 'restore',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'url_rewrites:write',
    'permission' =>
    array (
      0 => 'settings.manage',
    ),
  ),
  6 =>
  array (
    'method' => 'DELETE',
    'path' => '/api/v1/url-rewrites/{id:\\d+}',
    'controller' => 'Zoosper\\UrlRewrite\\Api\\UrlRewriteApiController',
    'action' => 'deletePermanently',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => 'url_rewrites:write',
    'permission' =>
    array (
      0 => 'settings.manage',
    ),
  ),
);
