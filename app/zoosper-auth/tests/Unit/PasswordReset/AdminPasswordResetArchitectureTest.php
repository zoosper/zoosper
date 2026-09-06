<?php

declare(strict_types=1);

it('keeps reset secrets hash-only and Auth-owned', function (): void {
    $root = dirname(__DIR__, 5);
    $service = (string) file_get_contents($root . '/app/zoosper-auth/src/PasswordReset/AdminPasswordResetService.php');
    $repository = (string) file_get_contents($root . '/app/zoosper-auth/src/PasswordReset/AdminPasswordResetTokenRepository.php');
    $schema = require $root . '/app/zoosper-auth/config/db_schema.php';

    expect($schema['tables'])->toHaveKey('admin_password_reset_tokens')
        ->and($service)->toContain("hash('sha256', " . '$plaintext' . ")")
        ->toContain('hash_equals(')
        ->not->toContain('Zoosper\\Mail\\')
        ->and($repository)->not->toContain('plaintext')->not->toContain('password_reset_url');
});
