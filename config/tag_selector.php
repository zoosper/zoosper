<?php

declare(strict_types=1);

return [
    /*
     * Progressive-enhancement tag selector configuration.
     *
     * The native checkbox fallback remains the source of truth. JavaScript only
     * improves the editing experience by rendering selected values as removable
     * tags and searchable options.
     */
    'enabled' => filter_var(env('ADMIN_TAG_SELECTOR_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
    'max_visible_options' => (int) env('ADMIN_TAG_SELECTOR_MAX_VISIBLE_OPTIONS', 25),
    'allow_search' => true,
    'allow_clear' => true,
];
