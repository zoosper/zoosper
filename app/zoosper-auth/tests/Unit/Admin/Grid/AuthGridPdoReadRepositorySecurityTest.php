<?php

declare(strict_types=1);

it('keeps Auth Grid PDO projections narrow and parameterised', function (): void {
    $root = dirname(__DIR__, 6);
    $users = (string) file_get_contents($root . '/app/zoosper-auth/src/Admin/Grid/PdoAdminUserGridReadRepository.php');
    $roles = (string) file_get_contents($root . '/app/zoosper-auth/src/Admin/Grid/PdoRoleGridReadRepository.php');

    expect($users)->toContain('u.id, u.name, u.email, u.status')
        ->toContain('bindValue(')
        ->not->toContain('password')
        ->not->toContain('two_factor')
        ->not->toContain('recovery')
        ->not->toContain('$_GET')
        ->and($roles)->toContain('r.id, r.label, r.code')
        ->toContain('bindValue(')
        ->not->toContain('permission')
        ->not->toContain('$_GET');
});










