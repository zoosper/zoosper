<?php

declare(strict_types=1);

return [
    /*
     * Canonical public asset base path.
     *
     * Keep application route namespaces such as /admin and /frontend separate
     * from static files. This prevents Nginx filesystem-directory collisions
     * and makes a future dynamic admin path easier to support.
     */
    'base_path' => rtrim((string) env('ASSET_BASE_PATH', '/assets'), '/'),
    'admin_path' => rtrim((string) env('ADMIN_ASSET_PATH', '/assets/admin'), '/'),
    'frontend_path' => rtrim((string) env('FRONTEND_ASSET_PATH', '/assets/frontend'), '/'),
    'module_path' => rtrim((string) env('MODULE_ASSET_PATH', '/assets/modules'), '/'),
];








