<?php

declare(strict_types=1);

it('owns an idempotent Media permission migration with ACL tree metadata', function (): void {
    $root = dirname(__DIR__, 3);
    $migration = (string) file_get_contents($root . '/database/migrations/202608140001_seed_media_permission.php');

    expect($migration)
        ->toContain('202608140001_seed_media_permission')
        ->toContain('media.manage')
        ->toContain('Manage media assets')
        ->toContain("'media'")
        ->toContain('admin_permissions')
        ->toContain('admin_role_permissions')
        ->toContain('INSERT IGNORE')
        ->toContain('INSERT OR IGNORE');
});
