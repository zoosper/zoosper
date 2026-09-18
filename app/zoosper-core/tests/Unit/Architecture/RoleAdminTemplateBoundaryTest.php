<?php

declare(strict_types=1);

it('keeps Role Admin rendering on the configured template boundary', function (): void {
    $root = dirname(__DIR__, 5);
    $controller = (string) file_get_contents(
        $root . '/app/zoosper-auth/src/Admin/Controller/RoleAdminController.php',
    );
    $controllers = (string) file_get_contents(
        $root . '/app/zoosper-auth/config/controllers.php',
    );

    expect($controller)
        ->toContain("'zoosper-auth::admin/roles/' . \$cleanTemplate")
        ->toContain('Role Admin rendering requires the configured template renderer.')
        ->not->toContain('extract(')
        ->not->toContain('ob_start(')
        ->not->toContain('require $path')
        ->not->toContain("'/zoosper-admin/resources/views/admin/roles/'")
        ->and($controllers)
        ->toContain("templates: \$services->get('theme.admin_template_renderer')")
        ->not->toContain("templates: \$services->has('theme.admin_template_renderer')");

    foreach (['form', 'index', 'permission-tree', 'user-assignment'] as $template) {
        expect($root . '/app/zoosper-auth/resources/views/admin/roles/' . $template . '.latte')
            ->toBeFile()
            ->and($root . '/app/zoosper-admin/resources/views/admin/roles/' . $template . '.php')
            ->not->toBeFile();
    }
});
