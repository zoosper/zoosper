<?php

declare(strict_types=1);

it('keeps migrated runtime consumers on the canonical Admin URL boundary', function (): void {
    $root = dirname(__DIR__, 5);
    $required = [
        '/app/zoosper-admin/src/Controller/AuditLogController.php' => "->url('audit-log')",
        '/app/zoosper-admin/src/Controller/LoginHistoryController.php' => "->url('login-history')",
        '/app/zoosper-admin/src/Controller/ThemeAdminController.php' => "->url('themes')",
        '/app/zoosper-mail/src/Controller/EmailLogAdminController.php' => "->url('mail-logs')",
        '/app/zoosper-site/src/Admin/Controller/SiteAdminController.php' => 'private function adminUrl(',
        '/app/zoosper-site/src/Admin/Controller/SiteDomainAdminController.php' => 'private function adminUrl(',
        '/app/zoosper-settings/src/Controller/SettingsCatalogueController.php' => "adminUrl('settings'",
        '/packages/zoosper-store-orders/src/Admin/StoreOrderGridWorkspace.php' => "->url('store-orders')",
        '/packages/zoosper-media/src/Controller/MediaAdminController.php' => "adminUrl('media'",
    ];

    foreach ($required as $file => $contract) {
        expect((string) file_get_contents($root . $file))->toContain($contract);
    }
});

it('keeps dynamic form actions out of migrated fallback templates', function (): void {
    $root = dirname(__DIR__, 5);
    $forbidden = [
        '/app/zoosper-theme/resources/views/admin/themes/index.php' => 'action="/admin/themes/assign"',
        '/app/zoosper-settings/resources/views/admin/settings/index.php' => 'action="/admin/settings',
        '/packages/zoosper-media/resources/views/admin/media/upload.php' => 'href="/admin/media"',
    ];

    foreach ($forbidden as $file => $literal) {
        expect((string) file_get_contents($root . $file))->not->toContain($literal);
    }
});
