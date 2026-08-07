<?php

declare(strict_types=1);

it('adds central idle timeout enforcement without weakening pending 2FA isolation', function (): void {
    $root = dirname(__DIR__, 5);
    $guard = (string) file_get_contents($root . '/app/zoosper-auth/src/Service/SessionGuard.php');
    $config = (string) file_get_contents($root . '/config/admin.php');
    $services = (string) file_get_contents($root . '/app/zoosper-auth/config/services.php');

    expect($guard)->toContain("SESSION_LAST_ACTIVITY_KEY = 'admin_last_activity_at'")
        ->toContain('private function expireIfIdle(): bool')
        ->toContain('private function touch(): void')
        ->toContain('private function clearAuthenticationState(): void')
        ->toContain('$lastActivity <= $now')
        ->toContain('unset($_SESSION[self::SESSION_PENDING_2FA_KEY])')
        ->and($config)->toContain("ADMIN_SESSION_IDLE_TIMEOUT")
        ->toContain("'session_idle_timeout' => \$idleTimeout")
        ->and($services)->toContain("get('admin.session_idle_timeout', 7200)");
});

it('cuts over remaining active Admin Mail Theme and Site runtime URLs', function (): void {
    $root = dirname(__DIR__, 5);
    $files = [
        '/app/zoosper-admin/src/Controller/AuditLogController.php',
        '/app/zoosper-admin/src/Controller/LoginHistoryController.php',
        '/app/zoosper-admin/src/Controller/ThemeAdminController.php',
        '/app/zoosper-mail/src/Controller/EmailLogAdminController.php',
        '/app/zoosper-site/src/Admin/Controller/SiteAdminController.php',
        '/app/zoosper-site/src/Admin/Controller/SiteDomainAdminController.php',
    ];
    foreach ($files as $file) {
        expect((string) file_get_contents($root . $file))->toContain('AdminUrlGenerator');
    }
    $theme = (string) file_get_contents($root . '/app/zoosper-theme/resources/views/admin/themes/index.php');
    expect($theme)->toContain('$e($assignUrl)')->not->toContain('action="/admin/themes/assign"');
});
