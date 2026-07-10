<?php

declare(strict_types=1);

$env = static function (string $key, mixed $default = null): mixed {
    if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }

    $value = getenv($key);
    return $value !== false && $value !== '' ? $value : $default;
};

return [
    /*
     * Default editor strategy for large admin content fields.
     *
     * Keep this configurable so deployments can choose textarea-only mode,
     * Editor.js block JSON, or a future Tiptap rich text adapter without
     * changing controller code.
     */
    'enabled' => filter_var($env('ADMIN_WYSIWYG_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'provider' => (string) $env('ADMIN_WYSIWYG_PROVIDER', 'editorjs'),
    'allow_toggle' => true,
    'store_format' => (string) $env('ADMIN_WYSIWYG_STORE_FORMAT', 'json'),
];
