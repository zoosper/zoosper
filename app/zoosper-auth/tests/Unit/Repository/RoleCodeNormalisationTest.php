<?php

declare(strict_types=1);

use Zoosper\Auth\Repository\RoleRepository;

it('normalises role codes consistently including the falsey zero string', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE admin_roles (id INTEGER PRIMARY KEY, code TEXT NOT NULL, label TEXT NOT NULL)');
    $insert = $pdo->prepare('INSERT INTO admin_roles (code, label) VALUES (?, ?)');
    $insert->execute(['editor_role', 'Editor']);
    $insert->execute(['', 'Empty']);
    $roles = new RoleRepository($pdo);

    expect($roles->findRoleByCode(' Editor - ROLE ')['label'])->toBe('Editor')
        ->and($roles->findRoleByCode('0')['label'])->toBe('Empty')
        ->and($roles->findRoleByCode('!!!')['label'])->toBe('Empty')
        ->and($roles->findRoleByCode('missing'))->toBeNull();
});