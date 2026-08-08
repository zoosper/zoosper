<?php

declare(strict_types=1);

it('keeps the admin 2FA reset confirmation compatible with enforcing CSP', function (): void {
    $root = dirname(__DIR__, 5);
    $template = (string) file_get_contents($root . '/app/zoosper-auth/resources/views/admin/users/form.latte');
    $assets = require $root . '/app/zoosper-auth/config/admin_assets.php';
    $script = (string) file_get_contents($root . '/public/assets/admin/js/admin-user-two-factor-reset.js');

    expect($template)->toContain('data-confirm-message="Reset 2FA for this admin user? They will need to enrol again."')
        ->not->toContain('onclick=')
        ->and($assets)->toHaveKey('zoosper-admin-user-two-factor-reset-runtime')
        ->and($assets['zoosper-admin-user-two-factor-reset-runtime']['path'])->toBe('/assets/admin/js/admin-user-two-factor-reset.js')
        ->and($script)->toContain("document.addEventListener('submit'")
        ->toContain('event.submitter')
        ->toContain('window.confirm(message)')
        ->toContain('event.preventDefault()');
});
