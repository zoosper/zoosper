<?php

declare(strict_types=1);

use Zoosper\Core\Testing\Upgrade\MySqlUpgradeDatabaseWorkspace;

test('mysql upgrade workspace is structurally random, quoted, and cleanup-first', function (): void {
    $source = (string) file_get_contents(dirname(__DIR__, 3) . '/src/Testing/Upgrade/MySqlUpgradeDatabaseWorkspace.php');
    expect($source)
        ->toContain("'zoosper_br2d_' . bin2hex(random_bytes(12))")
        ->toContain('CREATE DATABASE `')
        ->toContain('CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci')
        ->toContain('DROP DATABASE IF EXISTS `')
        ->toContain('finally')
        ->toContain('$this->drop()')
        ->not->toContain('DB_PASSWORD')
        ->not->toContain('echo $database');
});
