<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Grid\AdminUserGridDefinition;
use Zoosper\Auth\Admin\Grid\RoleGridDefinition;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Grid\GridColumnRegistry;

it('defines a secure configurable Admin Users Grid', function (): void {
    $definition = (new AdminUserGridDefinition())->build();

    expect(AdminUserGridDefinition::KEY)->toBe('admin.users')
        ->and($definition->allColumnKeys())->toBe(['id', 'name', 'email', 'status', 'actions'])
        ->and($definition->sortableColumnKeys())->toBe(['id', 'name', 'email', 'status'])
        ->and($definition->filterKeys())->toBe(['q', 'status'])
        ->and($definition->toggleableColumnKeys())->toBe(['name', 'email', 'status']);

    $root = dirname(__DIR__, 6);
    $source = (string) file_get_contents(
        $root . '/app/zoosper-auth/src/Admin/Grid/AdminUserGridDefinition.php',
    );

    expect($source)->not->toContain('password')
        ->not->toContain('two_factor')
        ->not->toContain('secret');
});

it('defines a secure configurable Roles Grid', function (): void {
    $definition = (new RoleGridDefinition())->build();

    expect(RoleGridDefinition::KEY)->toBe('admin.roles')
        ->and($definition->allColumnKeys())->toBe(['id', 'label', 'code', 'actions'])
        ->and($definition->sortableColumnKeys())->toBe(['id', 'label', 'code'])
        ->and($definition->filterKeys())->toBe(['q'])
        ->and($definition->toggleableColumnKeys())->toBe(['label', 'code']);
});

it('keeps both auth grids extensible through stable Grid keys', function (): void {
    $root = dirname(__DIR__, 6);
    $registry = new GridColumnRegistry(new ModuleRegistry($root));

    expect((new AdminUserGridDefinition($registry))->build())->not->toBeNull()
        ->and((new RoleGridDefinition($registry))->build())->not->toBeNull();
});
