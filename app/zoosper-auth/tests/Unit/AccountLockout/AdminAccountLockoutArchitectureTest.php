<?php

declare(strict_types=1);

it('keeps temporary lockout separate from Admin active status and raw credentials', function (): void {
    $root = dirname(__DIR__, 5);
    $schema = require $root . '/app/zoosper-auth/config/db_schema.php';
    $service = (string) file_get_contents($root . '/app/zoosper-auth/src/AccountLockout/AdminAccountLockoutService.php');
    $repository = (string) file_get_contents($root . '/app/zoosper-auth/src/AccountLockout/AdminAccountLockoutRepository.php');
    expect($schema['tables'])->toHaveKey('admin_account_lockouts')
        ->and($schema['tables']['admin_account_lockouts']['columns'])->not->toHaveKey('email')->not->toHaveKey('password')
        ->and($service)->not->toContain('updateStatus')->not->toContain("'inactive'")
        ->and($repository)->toContain('ON CONFLICT(admin_user_id) DO UPDATE')
        ->toContain('ON DUPLICATE KEY UPDATE');
});
