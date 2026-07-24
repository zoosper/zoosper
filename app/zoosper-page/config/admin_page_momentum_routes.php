<?php

declare(strict_types=1);

return array (
  'page_momentum_routes' => 
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
  ),
);
