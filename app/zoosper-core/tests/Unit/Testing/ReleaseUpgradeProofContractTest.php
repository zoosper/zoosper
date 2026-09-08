<?php

declare(strict_types=1);

it('keeps the release-upgrade proof isolated bounded and data-preserving', function (): void {
    $root = dirname(__DIR__, 5);
    $tool = (string) file_get_contents($root . '/tools/verify-release-upgrade.php');

    expect($tool)->toContain("get_current_user() !== 'vagrant'")
        ->toContain('worktree add --detach')
        ->toContain("sys_get_temp_dir() . '/zoosper-release-upgrade-'")
        ->toContain("new PDO('sqlite:'")
        ->toContain("PRAGMA foreign_key_check")
        ->toContain("'idempotent' => true")
        ->toContain('install --no-dev --no-interaction')
        ->toContain('COMPOSER_MAX_PARALLEL_HTTP=1')
        ->toContain('$attempt <= 3')
        ->toContain('removeWorktree($repository, $release)')
        ->toContain('removeWorktree($repository, $current)')
        ->not->toContain('git checkout ')
        ->not->toContain('git reset ')
        ->not->toContain('git clean ')
        ->not->toContain('/home/vagrant/zoosper/.env');
});
