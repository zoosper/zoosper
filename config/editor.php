<?php

declare(strict_types=1);

$env = static fn (string $key, mixed $default = null): mixed => $_ENV[$key] ?? getenv($key) ?: $default;

return [
    'default_editor' => (string) $env('CONTENT_EDITOR', 'editorjs'),
    'fallback_editor' => 'textarea',
    'allow_custom_editors' => true,
    'current_content_format' => 'html',
    'future_content_format' => 'block_json',
    'editorjs' => [
        'enabled' => filter_var($env('EDITORJS_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'library_loaded_by_theme' => false,
        'bundle_path' => '/assets/admin/js/editorjs.bundle.js',
        'bundle_source' => 'assets/admin/editor/zoosper-editorjs-entry.js',
        'build_command' => 'npm run build:admin-editor',
    ],
];
