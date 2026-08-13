<?php

declare(strict_types=1);

it('renders Role lifecycle actions only for an existing persisted Role ID', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Admin/Controller/RoleAdminController.php');

    expect($source)
        ->toContain('\'lifecycleHtml\' => $roleId !== null && $this->lifecycle !== null')
        ->toContain('$this->lifecycle->actionsHtml($roleId, (string) ($role[\'code\'] ?? \'\'))')
        ->not->toContain('actionsHtml($id,');
});

it('keeps create and edit Role forms on the same nullable persisted-role contract', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Admin/Controller/RoleAdminController.php');

    expect($source)
        ->toContain('$roleId = $role !== null ? (int) $role[\'id\'] : null;')
        ->toContain('$this->form($this->adminUrl(\'roles/create\'))')
        ->toContain('$this->form($this->adminUrl(\'roles/edit\', [\'id\' => (int) $role[\'id\']]), $role)');
});
