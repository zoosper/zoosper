<?php

declare(strict_types=1);

return [
    'admin.users.form' => [
        'fields' => [
            'name' => ['type' => 'text', 'label' => 'Name', 'required' => true, 'sort_order' => 10],
            'email' => ['type' => 'email', 'label' => 'Email', 'required' => true, 'sort_order' => 20],
            'status' => ['type' => 'select', 'label' => 'Status', 'sort_order' => 30, 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            'password' => ['type' => 'password', 'label' => 'Password', 'sort_order' => 40],
        ],
    ],
    'admin.roles.form' => [
        'fields' => [
            'label' => ['type' => 'text', 'label' => 'Role label', 'required' => true, 'sort_order' => 10],
            'code' => ['type' => 'text', 'label' => 'Role code', 'required' => true, 'sort_order' => 20],
        ],
    ],
];
