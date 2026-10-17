<?php

declare(strict_types=1);

return array (
  0 =>
  array (
    'method' => 'GET',
    'path' => '/sitemap.xml',
    'controller' => 'Zoosper\\Seo\\Controller\\SeoPublicController',
    'action' => 'sitemap',
    'public' => true,
    'stateless' => true,
    'auth' => 'anonymous',
  ),
  1 =>
  array (
    'method' => 'GET',
    'path' => '/robots.txt',
    'controller' => 'Zoosper\\Seo\\Controller\\SeoPublicController',
    'action' => 'robots',
    'public' => true,
    'stateless' => true,
    'auth' => 'anonymous',
  ),
);
