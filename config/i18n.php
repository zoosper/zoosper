<?php

declare(strict_types=1);

return [
    /**
     * Default locale used when a more specific locale is not configured.
     */
    'default_locale' => 'en_AU',

    /**
     * Admin locale used by the admin translator resolver until per-admin user
     * preferences are introduced.
     */
    'admin_locale' => 'en_AU',

    /**
     * Site/frontend locale placeholder used until per-site locale settings are
     * wired into SiteContext.
     */
    'site_locale' => 'en_AU',

    /**
     * Fallback locale loaded before the active locale.
     */
    'fallback_locale' => 'en_AU',
];
