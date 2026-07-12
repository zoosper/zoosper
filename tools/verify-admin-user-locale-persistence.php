<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$repositoryPath = find_file_containing($basePath, 'class AdminUserRepository');

print "Zoosper admin user locale persistence verification\n";
print "==================================================\n\n";

$controller = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
$repository = $repositoryPath !== null ? (string) file_get_contents($repositoryPath) : '';

$checks = [
    'UserAdminController exists' => is_file($controllerPath),
    'AdminUserRepository exists' => $repositoryPath !== null,
    'locale field is rendered in user admin form' => str_contains($controller, 'name="locale"') && str_contains($controller, 'admin-user-locale'),
    'submitted locale is normalised' => str_contains($controller, 'normaliseAdminLocale('),
    'AdminUser constructor receives normalised submitted locale when saving' => str_contains($controller, "locale: \$this->normaliseAdminLocale(\$_POST['locale'] ?? null)"),
    'repository writes locale on insert or update' => str_contains($repository, ':locale') && (str_contains($repository, 'locale = :locale') || str_contains($repository, ', locale')),
    'repository binds locale from AdminUser model' => str_contains($repository, "'locale' => \$user->locale") || str_contains($repository, '"locale" => $user->locale'),
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
