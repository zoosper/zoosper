<?php

declare(strict_types=1);

return array (
  'page_momentum_admin_hook' => 
  array (
    'enabled' => true,
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
    'source' => 'PageMomentumAdminAggregationBridge',
    'live_mutation' => false,
    'rollback' => 
    array (
      0 => 'remove hook-consumer adapter from the next runtime patch',
      1 => 'remove app/zoosper-page/config/admin_page_momentum_hook_candidate.php if needed',
      2 => 'set page momentum metadata flags to false only if activation must be reverted',
      3 => 'run Pest and inspect nginx/application logs',
    ),
  ),
);
