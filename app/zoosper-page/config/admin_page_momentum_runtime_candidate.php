<?php

declare(strict_types=1);

return array (
  'page_momentum_admin_integration' => 
  array (
    'enabled' => true,
    'source' => 'page-momentum-metadata',
    'routes' => 
    array (
      0 => 
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
    ),
    'menu_items' => 
    array (
      0 => 
      array (
        'label' => 'Page momentum',
        'route' => 'admin.page_momentum.index',
        'permission' => 'page.manage',
        'sort_order' => 95,
        'description' => 'Visible launch-readiness status for page/admin improvements.',
      ),
    ),
    'live_mutation' => false,
    'rollback' => 
    array (
      0 => 'remove this candidate config if runtime wiring fails',
      1 => 'set page_momentum_routes.enabled to false if metadata needs rollback',
      2 => 'set page_momentum_menu.enabled to false if metadata needs rollback',
      3 => 'rerun Pest and inspect nginx/application logs',
    ),
  ),
);
