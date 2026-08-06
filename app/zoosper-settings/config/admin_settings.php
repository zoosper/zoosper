<?php

declare(strict_types=1);

return [[
    'id' => 'system.admin',
    'label' => 'Administration',
    'category' => 'advanced',
    'description' => 'Core administration behaviour and paths.',
    'permission' => 'settings.manage',
    'sort_order' => 900,
    'groups' => [[
        'id' => 'interface',
        'label' => 'Interface',
        'description' => 'Settings catalogue display preferences.',
        'sort_order' => 100,
        'open_by_default' => true,
        'settings' => [[
            'path' => 'settings.catalogue.show_paths',
            'label' => 'Show configuration paths',
            'type' => 'boolean',
            'description' => 'Display technical configuration paths in the Settings catalogue.',
            'default' => true,
            'sort_order' => 100,
        ]],
    ], [
        'id' => 'routing',
        'label' => 'Routing',
        'description' => 'Administrative routing configuration.',
        'sort_order' => 200,
        'settings' => [[
            'path' => 'admin.base_path',
            'label' => 'Admin base path',
            'type' => 'text',
            'description' => 'Controlled by project configuration and shown read-only.',
            'read_only' => true,
            'sort_order' => 100,
        ]],
    ]],
]];
