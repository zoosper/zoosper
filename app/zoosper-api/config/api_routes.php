<?php

declare(strict_types=1);

return array (
  0 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/health',
    'controller' => 'Zoosper\\Api\\Controller\\HealthController',
    'action' => 'show',
    'public' => true,
    'stateless' => true,
    'auth' => 'anonymous',
  ),
  1 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/hello',
    'controller' => 'Zoosper\\Api\\Controller\\HelloController',
    'action' => 'show',
    'public' => true,
    'stateless' => true,
    'auth' => 'anonymous',
  ),
  2 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/auth/login',
    'controller' => 'Zoosper\\Api\\Controller\\AuthController',
    'action' => 'login',
    'public' => true,
    'auth' => 'anonymous',
  ),
  3 =>
  array (
    'method' => 'POST',
    'path' => '/api/v1/auth/logout',
    'controller' => 'Zoosper\\Api\\Controller\\AuthController',
    'action' => 'logout',
    'auth' => 'session',
    'public' => false,
    'stateless' => false,
  ),
  4 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/me',
    'controller' => 'Zoosper\\Api\\Controller\\MeController',
    'action' => 'show',
    'auth' => 'session',
    'public' => false,
    'stateless' => false,
  ),
  5 =>
  array (
    'method' => 'GET',
    'path' => '/api/v1/token/me',
    'controller' => 'Zoosper\\Api\\Controller\\TokenMeController',
    'action' => 'show',
    'public' => false,
    'stateless' => true,
    'auth' => 'bearer',
    'scope' => NULL,
    'permission' =>
    array (
    ),
  ),
);
