<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$repositoryPath = find_file_containing($basePath, 'class AdminUserRepository');

print "Zoosper UserAdminController pipeline locale persistence verification\n";
print "====================================================================\n\n";

$controller = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
$repository = $repositoryPath !== null ? (string) file_get_contents($repositoryPath) : '';

$checks = [
    'UserAdminController exists' => is_file($controllerPath),
    'AdminUserRepository exists' => $repositoryPath !== null,
    'controller has adminUserLocaleFromForm helper' => str_contains($controller, 'function adminUserLocaleFromForm('),
    'controller helper uses AdminUserSaveDataFactory' => str_contains($controller, 'AdminUserSaveDataFactory'),
    'controller passes locale helper to createWithRoleIds when present' => !str_contains($controller, 'createWithRoleIds(') || preg_match('/createWithRoleIds\([\s\S]*adminUserLocaleFromForm\(/', $controller) === 1,
    'controller passes locale helper to updateUser when present' => !str_contains($controller, 'updateUser(') || preg_match('/updateUser\([\s\S]*adminUserLocaleFromForm\(/', $controller) === 1,
    'repository createWithRoleIds accepts locale' => preg_match('/function createWithRoleIds\([^)]*\?string \$locale = null/', $repository) === 1,
    'repository updateUser accepts locale' => preg_match('/function updateUser\([^)]*\?string \$locale = null/', $repository) === 1,
    'repository create SQL writes locale' => str_contains($repository, 'INSERT INTO admin_users (email, name, password_hash, status, locale,'),
    'repository update SQL writes locale' => str_contains($repository, 'status = :status, locale = :locale'),
    'repository binds locale parameter' => str_contains($repository, "'locale' => \$locale"),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nRepository: " . ($repositoryPath !== null ? relative_path($basePath, $repositoryPath) : 'not found') . PHP_EOL;
print "Result: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);

function find_file_containing(string $basePath, string $needle): ?string
{
    foreach ([$basePath . '/app', $basePath . '/modules'] as $root) {
        if (!is_dir($root)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->getExtension() === 'php') {
                $path = $file->getPathname();
                if (str_contains((string) file_get_contents($path), $needle)) {
                    return $path;
                }
            }
        }
    }

    return null;
}

function relative_path(string $basePath, string $path): string
{
    return str_starts_with($path, $basePath . '/') ? substr($path, strlen($basePath) + 1) : $path;
}
