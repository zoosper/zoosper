<?php

declare(strict_types=1);

it('migrates Settings and role administration without changing their security owners', function (): void {
    $root = dirname(__DIR__, 5);
    $roles = (string) file_get_contents($root . '/app/zoosper-admin/resources/views/admin/roles/index.php');
    $form = (string) file_get_contents($root . '/app/zoosper-admin/resources/views/admin/roles/form.php');
    $settings = (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $components = (string) file_get_contents($root . '/app/zoosper-admin/resources/assets/css/admin-components.css');

    expect($roles)->toContain('class="page-header admin-role-header"')
        ->toContain('role="region" aria-label="Roles and permissions" tabindex="0"')
        ->toContain('<thead>')
        ->toContain('<tbody>')
        ->and($form)->toContain('method="post"')
        ->toContain('name="_csrf_token"')
        ->toContain('class="admin-role-form"')
        ->toContain('class="admin-role-actions"')
        ->not->toMatch('/\son[a-z]+\s*=/i')
        ->not->toMatch('/\sstyle\s*=/i')
        ->and($settings)->toContain('/* Fable workspace migration: quieter Settings catalogue')
        ->toContain('grid-template-columns: 15.5rem minmax(0,1fr)')
        ->toContain('@media (prefers-reduced-motion:reduce)')
        ->and($components)->toContain('/* Fable workspace migration: role and permission administration. */');
});
