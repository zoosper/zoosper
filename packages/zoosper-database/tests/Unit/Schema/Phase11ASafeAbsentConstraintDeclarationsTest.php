<?php
declare(strict_types=1);

it('declares the ten audited safe absent relationships in their owner manifests', function (): void {
    $root = dirname(__DIR__, 5);
    $expected = [
        'app/zoosper-admin/config/db_schema.php' => ['admin_dashboard_preferences' => ['fk_admin_dashboard_preferences_user']],
        'app/zoosper-global-announcements/config/db_schema.php' => ['admin_announcement_acknowledgments' => ['fk_announcement_ack_user']],
        'app/zoosper-page/config/db_schema.php' => ['page_site_assignments' => ['fk_page_site_assignments_page', 'fk_page_site_assignments_site']],
        'app/zoosper-two-factor/config/db_schema.php' => [
            'admin_user_two_factor' => ['fk_admin_user_two_factor_user'],
            'admin_user_recovery_codes' => ['fk_admin_user_recovery_codes_user'],
            'admin_two_factor_challenges' => ['fk_admin_two_factor_challenges_user'],
        ],
        'app/zoosper-url-rewrite/config/db_schema.php' => ['url_rewrites' => ['fk_url_rewrites_site']],
        'packages/zoosper-admin-grid/config/db_schema.php' => [
            'admin_grid_preferences' => ['fk_admin_grid_preferences_user'],
            'admin_grid_bookmarks' => ['fk_admin_grid_bookmarks_user'],
        ],
    ];
    foreach ($expected as $relative => $tables) {
        $schema = require $root . '/' . $relative;
        foreach ($tables as $table => $foreignKeys) {
            expect($schema['tables'][$table]['foreign_keys'] ?? [])->toHaveKeys($foreignKeys);
            foreach ($foreignKeys as $foreignKey) {
                expect($schema['tables'][$table]['foreign_keys'][$foreignKey]['on_delete'])->toBe('CASCADE');
            }
        }
    }
});
