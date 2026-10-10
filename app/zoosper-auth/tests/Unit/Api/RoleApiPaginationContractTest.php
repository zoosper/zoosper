<?php

declare(strict_types=1);

it('bounds Role collection reads and publishes canonical pagination metadata', function (): void {
    $root = dirname(__DIR__, 3);
    $controller = (string) file_get_contents($root . '/src/Api/RoleApiController.php');
    $repository = (string) file_get_contents($root . '/src/Repository/RoleRepository.php');
    expect($controller)
        ->toContain('pageForApi(Pager::fromQuery([')
        ->toContain("'pagination' => \$this->normalisePagination(\$result)")
        ->and($repository)
        ->toContain('public function pageForApi(Pager $requested): PaginationResult')
        ->toContain('SELECT COUNT(*) FROM admin_roles')
        ->toContain('LIMIT :limit OFFSET :offset')
        ->toContain("relationIdsByRole('admin_role_permissions', 'permission_id', \$roleIds)")
        ->toContain("relationIdsByRole('admin_user_roles', 'user_id', \$roleIds)");
});
