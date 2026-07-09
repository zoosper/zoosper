<?php
declare(strict_types=1);
require dirname(__DIR__) . '/bootstrap/autoload.php';

use Zoosper\Auth\Access\Permission;
use Zoosper\Auth\Service\PasswordHasher;

$h = new PasswordHasher();
$hash = $h->hash('secret');
if (!$h->verify('secret', $hash)) {
    fwrite(STDERR, "Password hashing failed\n");
    exit(1);
}
if (Permission::AdminAccess->value !== 'admin.access') {
    fwrite(STDERR, "Permission enum failed\n");
    exit(1);
}
echo "Zoosper phase 0.2 smoke tests passed.\n";
