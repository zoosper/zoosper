<?php

declare(strict_types=1);

it('uses only the declared Login History statuses for presentation', function (): void {
    $root = dirname(__DIR__, 3);
    $definition = (string) file_get_contents($root . '/src/Admin/LoginHistoryGrid.php');
    $script = (string) file_get_contents($root . '/resources/admin/js/login-history-workspace.js');
    $css = (string) file_get_contents($root . '/resources/admin/css/login-history-workspace.css');

    foreach (['success', 'failed', 'password_ok_pending_2fa', 'otp_failed'] as $status) {
        expect($definition)->toContain("'value' => '{$status}'")
            ->and($css)->toContain(".login-history-index__status--{$status}");
    }

    expect($script)
        ->toContain("row.dataset.loginHistoryRowEnhanced === 'true'")
        ->toContain("row.dataset.loginHistoryRowEnhanced = 'true'")
        ->toContain('td[data-grid-column="email"]')
        ->toContain('td[data-grid-column="status"]')
        ->toContain('td[data-grid-column="ip_address"]')
        ->not->toContain('page_size')
        ->not->toContain('pageSize')
        ->and($css)
        ->toContain(':root[data-admin-theme="dark"]')
        ->toContain(':root[data-admin-theme="ocean"]')
        ->toContain('@media (prefers-contrast: more)');
});

it('keeps Login History presentation out of Audit Log and shared Grid assets', function (): void {
    $repo = dirname(__DIR__, 5);
    $audit = (string) file_get_contents($repo . '/packages/zoosper-audit/resources/admin/css/audit-log-workspace.css');
    $shared = (string) file_get_contents($repo . '/packages/zoosper-admin-grid/resources/admin/css/grid-admin-polish.css');

    expect($audit)->not->toContain('.login-history-index')
        ->and($shared)->not->toContain('.login-history-index');
});
