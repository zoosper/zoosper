<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$userAdminController = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$repositoryPath = find_file_containing($basePath, 'class AdminUserRepository') ?? find_file_containing($basePath, 'admin_users');

print "Zoosper admin user locale persistence readiness verification\n";
print "============================================================\n\n";

$controller = is_file($userAdminController) ? (string) file_get_contents($userAdminController) : '';
$repository = $repositoryPath !== null ? (string) file_get_contents($repositoryPath) : '';

$checks = [
    'UserAdminController exists' => is_file($userAdminController),
    'locale field is present in user admin form rendering' => str_contains($controller, 'name="locale"') && str_contains($controller, 'renderAdminLocaleField('),
    'locale is read from submitted values for UI state' => str_contains($controller, "\$submitted['locale']") || str_contains($controller, '$submitted["locale"]'),
    'AdminUserRepository or admin_users persistence file found' => $repositoryPath !== null,
    'repository/source references locale column' => str_contains($repository, 'locale'),
    'repository/source references admin_users table' => str_contains($repository, 'admin_users'),
];

$warning = false;
$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'WARN') . PHP_EOL;
    $warning = $warning || !$ok;
}

print "\nRepository candidate: " . ($repositoryPath !== null ? relative_path($basePath, $repositoryPath) : 'not found') . PHP_EOL;
print "Result: " . ($failed ? 'FAIL' : ($warning ? 'WARN' : 'OK')) . PHP_EOL;
exit(0);

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
