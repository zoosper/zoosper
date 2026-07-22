<?php

declare(strict_types=1);

return [
    ['code' => 'dashboard', 'label' => 'Dashboard', 'url' => '/admin', 'permission' => 'admin.access', 'sort_order' => 10, 'group' => 'Content'],
    ['code' => 'audit-log', 'label' => 'Audit Log', 'url' => '/admin/audit-log', 'permission' => 'role.manage', 'sort_order' => 70, 'group' => 'System'],
    ['code' => 'login-history', 'label' => 'Login History', 'url' => '/admin/login-history', 'permission' => 'role.manage', 'sort_order' => 71, 'group' => 'System'],
    ['code' => 'settings', 'label' => 'Settings', 'url' => '/admin/settings', 'permission' => 'settings.manage', 'sort_order' => 90, 'group' => 'System'],
];
