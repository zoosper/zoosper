<?php

declare(strict_types=1);

return [
    ['code' => 'admin-users', 'label' => 'Admin Users', 'url' => '/admin/users', 'permission' => 'user.manage', 'sort_order' => 10, 'group' => 'Users', 'icon' => 'users'],
    ['code' => 'admin-roles', 'label' => 'Roles & Permissions', 'url' => '/admin/roles', 'permission' => 'role.manage', 'sort_order' => 20, 'group' => 'Users', 'icon' => 'roles'],
    ['code' => 'access-tokens', 'label' => 'Access Tokens', 'url' => '/admin/access-tokens', 'sort_order' => 30, 'group' => 'Users', 'icon' => 'access-tokens'],
];
