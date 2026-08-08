<?php

declare(strict_types=1);

if (!function_exists('settingsPresentationBundle')) {
    /**
     * Load the complete Settings presentation contract used by architecture tests.
     *
     * The production boundary is intentionally split across semantic template,
     * stylesheet and deferred browser runtime. Tests that assert cross-layer UI
     * behaviour should use this helper instead of rebuilding that bundle locally.
     */
    function settingsPresentationBundle(string $root): string
    {
        return (string) file_get_contents($root . '/app/zoosper-settings/resources/views/admin/settings/index.php')
            . (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css')
            . (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    }
}
