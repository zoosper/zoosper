<?php

declare(strict_types=1);

it('keeps the mysql release upgrade proof isolated explicit and preservation-focused', function (): void {
    $tool = (string) file_get_contents(dirname(__DIR__, 5) . '/tools/verify-mysql-release-upgrade.php');
    expect($tool)
        ->toContain("trim((string) shell_exec('id -un')) !== 'vagrant'")
        ->toContain('BR2D_MYSQL_PASSWORD')
        ->toContain('MySqlUpgradeDatabaseWorkspace')
        ->toContain('worktree add --detach')
        ->toContain("'/zoosper-mysql-release-upgrade-'")
        ->toContain('install --no-dev --no-interaction')
        ->toContain('PDO::ATTR_EMULATE_PREPARES => false')
        ->toContain('schema:foreign-keys:apply --confirm=apply')
        ->toContain('INFORMATION_SCHEMA.COLUMNS')
        ->toContain('orphanCount($pdo)')
        ->toContain('foreignKeyCount($pdo)')
        ->toContain("'idempotent' => true")
        ->toContain('database_dropped')
        ->toContain('removeWorktree($repository, $release)')
        ->toContain('removeWorktree($repository, $current)')
        ->not->toContain('/home/vagrant/zoosper/.env')
        ->not->toContain('git checkout ')
        ->not->toContain('git reset ')
        ->not->toContain('git clean ');
});
