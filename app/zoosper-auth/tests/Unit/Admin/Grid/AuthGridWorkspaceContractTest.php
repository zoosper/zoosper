<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Grid\AdminUserGridDefinition;
use Zoosper\Auth\Admin\Grid\AdminUserGridWorkspace;
use Zoosper\Auth\Admin\Grid\AuthGridWorkspace;
use Zoosper\Auth\Admin\Grid\RoleGridDefinition;
use Zoosper\Auth\Admin\Grid\RoleGridWorkspace;

it('requires authenticated identity for both Auth Grid workspaces', function (): void {
    foreach ([AdminUserGridWorkspace::class, RoleGridWorkspace::class] as $workspace) {
        $parameters = (new \ReflectionMethod($workspace, 'resolve'))->getParameters();

        expect($parameters[0]->getName())->toBe('adminUserId')
            ->and((string) $parameters[0]->getType())->toBe('int');
    }
});

it('keeps Auth Grid identities and actions server-owned', function (): void {
    $root = dirname(__DIR__, 6);
    $adminUsers = (string) file_get_contents($root . '/app/zoosper-auth/src/Admin/Grid/AdminUserGridWorkspace.php');
    $roles = (string) file_get_contents($root . '/app/zoosper-auth/src/Admin/Grid/RoleGridWorkspace.php');

    expect(AdminUserGridDefinition::KEY)->toBe('admin.users')
        ->and(RoleGridDefinition::KEY)->toBe('admin.roles')
        ->and($adminUsers)->toContain("\$this->adminUrls?->url('users') ?? '/admin/users'")
        ->and($roles)->toContain("\$this->adminUrls?->url('roles') ?? '/admin/roles'");
});

it('renders controls from the complete definition while returning filtered table state', function (): void {
    $root = dirname(__DIR__, 6);
    $source = (string) file_get_contents(
        $root . '/app/zoosper-auth/src/Admin/Grid/AuthGridWorkspace.php',
    );

    expect($source)->toContain("'state' => \$state")
        ->toContain('definition: $orderedDefinition')
        ->toContain('$this->renderer->render($controlState, $action)')
        ->not->toContain('$_GET')
        ->not->toContain('$_POST');
});
