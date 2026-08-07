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
        'id' => 'security',
        'label' => 'Security',
        'description' => 'Environment-controlled Admin session security.',
        'sort_order' => 150,
        'settings' => [[
            'path' => 'admin.session_idle_timeout',
            'label' => 'Session idle timeout (seconds)',
            'type' => 'text',
            'description' => 'Controlled by ADMIN_SESSION_IDLE_TIMEOUT. Default 7200 seconds; 0 disables idle expiry.',
            'default' => 7200,
            'read_only' => true,
            'sort_order' => 100,
        ], [
            'path' => 'session.lifetime_seconds',
            'label' => 'Session cookie lifetime (seconds)',
            'type' => 'text',
            'description' => 'Controlled by SESSION_LIFETIME_SECONDS. Bounded from 300 to 604800 seconds.',
            'default' => 28800,
            'read_only' => true,
            'sort_order' => 200,
        ], [
            'path' => 'session.samesite',
            'label' => 'Session SameSite policy',
            'type' => 'text',
            'description' => 'Controlled by SESSION_SAMESITE. Accepted values are Lax, Strict and None; invalid values fall back to Lax.',
            'default' => 'Lax',
            'read_only' => true,
            'sort_order' => 300,
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
