<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/autoload.php';

use Zoosper\Auth\Access\InMemoryRoleProvider;
use Zoosper\Auth\Access\Permission;

$roles = InMemoryRoleProvider::createDefault();
$superAdmin = $roles->get('super_admin');

if ($superAdmin === null || !$superAdmin->allows(Permission::AdminAccess)) {
    fwrite(STDERR, "Role permission smoke test failed.\n");
    exit(1);
}

fwrite(STDOUT, "Zoosper smoke tests passed.\n");
