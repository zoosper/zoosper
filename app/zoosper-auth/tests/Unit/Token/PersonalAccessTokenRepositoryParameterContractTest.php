<?php

declare(strict_types=1);

it('uses one unique named PDO placeholder per bound value occurrence', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Token/PersonalAccessTokenRepository.php');

    expect($source)
        ->toContain('last_used_at=:last_used_at,updated_at=:updated_at')
        ->toContain("['last_used_at'=>\$now,'updated_at'=>\$now,'id'=>\$id,'cutoff'=>\$cutoff]")
        ->toContain('last_used_at IS NULL OR last_used_at<:cutoff')
        ->toContain('revoked_at=:revoked_at,updated_at=:updated_at')
        ->toContain("['revoked_at'=>\$now,'updated_at'=>\$now,'id'=>\$id,'owner'=>\$ownerId]")
        ->not->toContain('last_used_at=:now,updated_at=:now')
        ->not->toContain('revoked_at=:now,updated_at=:now');
});










