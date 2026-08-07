<?php

declare(strict_types=1);

it('closes the live Auth Grid factory definition workspace and page action path', function (): void {
    $root = dirname(__DIR__, 5);
    $factory = (string) file_get_contents($root . '/app/zoosper-auth/src/Admin/Grid/AuthGridPageBuilderFactory.php');
    $services = (string) file_get_contents($root . '/app/zoosper-auth/config/services_auth_grid.php');
    $usersPage = (string) file_get_contents($root . '/app/zoosper-auth/src/Admin/Grid/AdminUserGridPageBuilder.php');
    $rolesPage = (string) file_get_contents($root . '/app/zoosper-auth/src/Admin/Grid/RoleGridPageBuilder.php');

    expect($factory)->toContain('private ?AdminUrlGenerator $adminUrls = null')
        ->toContain('new AdminUserGridDefinition($this->columnRegistry, $this->adminUrls)')
        ->toContain('new RoleGridDefinition($this->columnRegistry, $this->adminUrls)')
        ->toContain('adminUrls: $this->adminUrls')
        ->and($services)->toContain('adminUrls: $services->get(AdminUrlGenerator::class)')
        ->and($usersPage)->toContain('$this->workspace->action()')
        ->and($rolesPage)->toContain('$this->workspace->action()');
});

it('retires fixed public workspace action constants without losing compatibility fallbacks', function (): void {
    $root = dirname(__DIR__, 5);
    foreach (['AdminUserGridWorkspace.php', 'RoleGridWorkspace.php'] as $file) {
        $source = (string) file_get_contents($root . '/app/zoosper-auth/src/Admin/Grid/' . $file);
        expect($source)->not->toContain('public const ACTION')
            ->toContain('public function action(): string')
            ->toContain('private ?AdminUrlGenerator $adminUrls = null');
    }
});
