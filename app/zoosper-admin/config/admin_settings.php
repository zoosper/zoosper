<?php

declare(strict_types=1);

/** Admin owns content-editor runtime selection metadata. */
return [[
    'id' => 'admin.content_editor',
    'label' => 'Content Editor',
    'category' => 'content',
    'description' => 'Default and fallback editors used by CMS content forms.',
    'permission' => 'settings.manage',
    'sort_order' => 150,
    'groups' => [[
        'id' => 'selection',
        'label' => 'Editor Selection',
        'description' => 'Built-in editor selection. Custom editor codes remain supported through project configuration and module services.',
        'sort_order' => 100,
        'open_by_default' => true,
        'settings' => [[
            'path' => 'editor.default_editor',
            'label' => 'Default content editor',
            'type' => 'select',
            'description' => 'Preferred registered editor for admin content fields.',
            'default' => 'editorjs',
            'options' => ['editorjs', 'textarea'],
            'read_only' => true,
            'sort_order' => 100,
        ], [
            'path' => 'editor.fallback_editor',
            'label' => 'Fallback content editor',
            'type' => 'select',
            'description' => 'Registered editor used when the preferred editor is unavailable.',
            'default' => 'textarea',
            'options' => ['textarea', 'editorjs'],
            'read_only' => true,
            'sort_order' => 200,
        ]],
    ]],
]];
