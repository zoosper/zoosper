<?php

declare(strict_types=1);

return array (
  0 => 
  array (
    'method' => 'GET',
    'path' => '/admin/pages',
    'controller' => 'Zoosper\\Admin\\Controller\\PageAdminController',
    'action' => 'index',
    'permission' => 'page.manage',
  ),
  1 => 
  array (
    'method' => 'GET',
    'path' => '/admin/pages/create',
    'controller' => 'Zoosper\\Admin\\Controller\\PageAdminController',
    'action' => 'createForm',
    'permission' => 'page.manage',
  ),
  2 => 
  array (
    'method' => 'POST',
    'path' => '/admin/pages/create',
    'controller' => 'Zoosper\\Admin\\Controller\\PageAdminController',
    'action' => 'create',
    'permission' => 'page.manage',
  ),
  3 => 
  array (
    'method' => 'GET',
    'path' => '/admin/pages/edit',
    'controller' => 'Zoosper\\Admin\\Controller\\PageAdminController',
    'action' => 'editForm',
    'permission' => 'page.manage',
  ),
  4 => 
  array (
    'method' => 'POST',
    'path' => '/admin/pages/edit',
    'controller' => 'Zoosper\\Admin\\Controller\\PageAdminController',
    'action' => 'update',
    'permission' => 'page.manage',
  ),
  5 => 
  array (
    'method' => 'GET',
    'path' => '/admin/pages/preview',
    'controller' => 'Zoosper\\Admin\\Controller\\PageAdminController',
    'action' => 'preview',
    'permission' => 'page.manage',
  ),
  6 => 
  array (
    'method' => 'POST',
    'path' => '/admin/pages/publish',
    'controller' => 'Zoosper\\Admin\\Controller\\PageAdminController',
    'action' => 'publish',
    'permission' => 'page.manage',
  ),
  7 => 
  array (
    'method' => 'POST',
    'path' => '/admin/pages/unpublish',
    'controller' => 'Zoosper\\Admin\\Controller\\PageAdminController',
    'action' => 'unpublish',
    'permission' => 'page.manage',
  ),
  8 => 
  array (
    'name' => 'admin.page_momentum.index',
    'method' => 'GET',
    'path' => '/admin/page-momentum',
    'controller' => 'Zoosper\\Page\\Admin\\Controller\\PageMomentumAdminController',
    'action' => 'index',
    'view' => 'admin/page-momentum.latte',
    'permission' => 'page.manage',
    'description' => 'Read-only launch-readiness panel for page/admin momentum.',
  ),
);
