<?php

declare(strict_types=1);

return array (
  0 => 
  array (
    'code' => 'pages',
    'label' => 'Pages',
    'url' => '/admin/pages',
    'permission' => 'page.manage',
    'sort_order' => 20,
    'group' => 'Content',
  ),
  1 => 
  array (
    'label' => 'Page momentum',
    'route' => 'admin.page_momentum.index',
    'permission' => 'page.manage',
    'sort_order' => 95,
    'description' => 'Visible launch-readiness status for page/admin improvements.',
  ),
);
