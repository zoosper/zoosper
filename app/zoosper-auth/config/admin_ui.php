<?php

declare(strict_types=1);

return [
    'admin.users.form' => [
        'sections' => [
            'identity' => ['title' => 'Identity', 'description' => 'The name and email shown throughout the Admin experience.'],
            'preferences' => ['title' => 'Admin preferences', 'description' => 'User-specific presentation defaults for the administration interface.'],
        ],
        'fields' => [
            'name' => ['type' => 'text', 'label' => 'Name', 'required' => true, 'sort_order' => 10, 'section' => 'identity'],
            'email' => ['type' => 'email', 'label' => 'Email', 'required' => true, 'sort_order' => 20, 'section' => 'identity'],
            'status' => ['type' => 'select', 'label' => 'Status', 'sort_order' => 30, 'section' => 'identity', 'options' => ['active' => 'Active', 'disabled' => 'Disabled']],
            'password' => ['type' => 'password', 'label' => 'Password', 'sort_order' => 40, 'section' => 'identity'],
            'locale' => ['type' => 'select', 'label' => 'Admin interface locale', 'sort_order' => 50, 'section' => 'preferences', 'options' => ['' => 'Use configured admin locale', 'en_AU' => 'English (Australia)']],
        ],
    ],
    'admin.roles.form' => [
        'fields' => [
            'label' => ['type' => 'text', 'label' => 'Role label', 'required' => true, 'sort_order' => 10],
            'code' => ['type' => 'text', 'label' => 'Role code', 'required' => true, 'sort_order' => 20],
        ],
    ],
];










