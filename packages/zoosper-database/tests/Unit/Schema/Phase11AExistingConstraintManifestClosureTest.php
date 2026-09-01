<?php
declare(strict_types=1);

it('mirrors existing migration-owned foreign keys in declarative manifests', function (): void {
    $root = dirname(__DIR__, 5);
    $admin = require $root . '/app/zoosper-admin/config/db_schema.php';
    $page = require $root . '/app/zoosper-page/config/db_schema.php';
    $media = require $root . '/packages/zoosper-media/config/db_schema.php';

    expect($admin['tables']['admin_login_history']['foreign_keys'])
        ->toHaveKey('fk_admin_login_history_user')
        ->and($admin['tables']['admin_activity_log']['foreign_keys'])
        ->toHaveKey('fk_admin_activity_log_user');

    expect($page['tables']['page_revisions']['columns'])->toHaveKey('created_by')
        ->and($page['tables']['page_revisions']['foreign_keys'])->toHaveKey('fk_page_revisions_created_by')
        ->and($page['tables']['pages']['foreign_keys'])->toHaveKeys([
            'fk_pages_site',
            'fk_pages_created_by',
            'fk_pages_updated_by',
        ])
        ->and($page['tables']['pages']['columns'])->not->toHaveKey('featured_image_id');

    expect($media['tables']['media_processing_queue']['foreign_keys'])
        ->toHaveKey('fk_media_processing_queue_asset');

    $foreignKeys = [
        $admin['tables']['admin_login_history']['foreign_keys']['fk_admin_login_history_user'],
        $admin['tables']['admin_activity_log']['foreign_keys']['fk_admin_activity_log_user'],
        $page['tables']['page_revisions']['foreign_keys']['fk_page_revisions_created_by'],
        $page['tables']['pages']['foreign_keys']['fk_pages_site'],
        $page['tables']['pages']['foreign_keys']['fk_pages_created_by'],
        $page['tables']['pages']['foreign_keys']['fk_pages_updated_by'],
        $media['tables']['media_processing_queue']['foreign_keys']['fk_media_processing_queue_asset'],
    ];
    foreach ($foreignKeys as $foreignKey) {
        expect($foreignKey['on_update'])->toBe('NO ACTION');
    }
});
