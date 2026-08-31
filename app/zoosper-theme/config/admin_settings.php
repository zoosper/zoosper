<?php

declare(strict_types=1);

/** Theme owns runtime template metadata; Admin remains read-only in Phase 9F2. */
return [[
    'id' => 'theme.rendering',
    'label' => 'Theme Rendering',
    'category' => 'design',
    'description' => 'Template engine and compiled-template storage configuration.',
    'permission' => 'settings.manage',
    'sort_order' => 100,
    'groups' => [[
        'id' => 'engine',
        'label' => 'Template Engine',
        'description' => 'Current rendering engine selection.',
        'sort_order' => 100,
        'open_by_default' => true,
        'settings' => [[
            'path' => 'template.engine',
            'label' => 'Default template engine',
            'type' => 'select',
            'description' => 'Current project-selected template rendering engine.',
            'default' => 'latte',
            'options' => ['latte', 'php'],
            'read_only' => true,
            'sort_order' => 100,
        ]],
    ], [
        'id' => 'cache',
        'label' => 'Compiled Templates',
        'description' => 'Storage used for compiled template output.',
        'sort_order' => 200,
        'settings' => [[
            'path' => 'template.template_cache_path',
            'label' => 'Template cache path',
            'type' => 'text',
            'description' => 'Project-relative path used by the Latte template engine cache.',
            'default' => 'var/cache/templates',
            'read_only' => true,
            'sort_order' => 100,
        ]],
    ]],
]];










