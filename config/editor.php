<?php

declare(strict_types=1);

$env = static fn (string $key, mixed $default = null): mixed => $_ENV[$key] ?? getenv($key) ?: $default;

return [
    /*
     * Default admin content editor code. Editor.js is the preferred modern
     * direction, but textarea fallback remains available and is used whenever
     * the JavaScript editor library is unavailable.
     */
    'default_editor' => (string) $env('CONTENT_EDITOR', 'editorjs'),
    'fallback_editor' => 'textarea',
    'allow_custom_editors' => true,

    /*
     * Current persistence format remains sanitised HTML. A later content-model
     * phase should add block_json storage and block rendering.
     */
    'current_content_format' => 'html',
    'future_content_format' => 'block_json',

    /*
     * Editor.js itself is not bundled in this phase. The adapter renders safe
     * hooks and keeps textarea as source of truth until local npm/Vite asset
     * packaging is added.
     */
    'editorjs' => [
        'enabled' => filter_var($env('EDITORJS_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'library_loaded_by_theme' => filter_var($env('EDITORJS_LIBRARY_LOADED_BY_THEME', false), FILTER_VALIDATE_BOOLEAN),
    ],
];
