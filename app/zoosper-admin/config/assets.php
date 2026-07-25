<?php

declare(strict_types=1);

/**
 * Admin module asset manifest.
 *
 * Registers the admin module's public asset directory so files under
 * resources/assets are served via the modular asset pipeline, e.g.
 *   asset('zoosper-admin', 'css/page-momentum.css')
 *
 * @return array<string, string> logical module name => absolute assets dir
 */

return [
    'zoosper-admin' => dirname(__DIR__) . '/resources/assets',
];
