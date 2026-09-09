<?php

declare(strict_types=1);

test('mysql upgrade workspace is structurally random, quoted, and cleanup-first', function (): void {
    $workspace = (string) file_get_contents(dirname(__DIR__, 3) . '/src/Testing/Upgrade/MySqlUpgradeDatabaseWorkspace.php');
    $capability = (string) file_get_contents(dirname(__DIR__, 5) . '/tools/verify-mysql-upgrade-capability.php');

    expect($workspace)
        ->toContain("'zoosper_br2d_' . bin2hex(random_bytes(12))")
        ->toContain('CREATE DATABASE `')
        ->toContain('CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci')
        ->toContain('DROP DATABASE IF EXISTS `')
        ->toContain('finally')
        ->toContain('$this->drop()')
        ->toContain('public function cleanupCompleted(): bool')
        ->toContain('INFORMATION_SCHEMA.SCHEMATA')
        ->not->toContain('DB_PASSWORD')
        ->not->toContain('echo $database');

    expect($capability)
        ->toContain('database_dropped')
        ->toContain('$result')
        ->toContain('if (!$workspace->cleanupCompleted())');
});
