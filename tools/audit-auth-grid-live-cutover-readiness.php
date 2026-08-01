<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

/** @return list<string> */
$findFiles = static function (string $base, string $name): array {
    if (!is_dir($base)) {
        return [];
    }

    $found = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() === $name) {
            $found[] = $file->getPathname();
        }
    }

    sort($found);

    return $found;
};

$assertSingleTarget = static function (
    string $label,
    array $paths,
    array $signals,
) use (&$errors, $root): ?string {
    if (count($paths) !== 1) {
        $relative = array_map(
            static fn (string $path): string => ltrim(str_replace($root, '', $path), '/'),
            $paths,
        );
        $errors[] = $label . ' expected exactly one target; found '
            . count($paths) . ($relative === [] ? '' : ': ' . implode(', ', $relative));
        return null;
    }

    $path = $paths[0];
    $source = (string) file_get_contents($path);
    foreach ($signals as $signal) {
        if (!str_contains($source, $signal)) {
            $errors[] = "Missing signal '{$signal}' in "
                . ltrim(str_replace($root, '', $path), '/');
        }
    }

    return $path;
};

$userController = $assertSingleTarget(
    'UserAdminController',
    $findFiles($root . '/app', 'UserAdminController.php'),
    ['class UserAdminController', 'function index'],
);
$roleController = $assertSingleTarget(
    'RoleAdminController',
    $findFiles($root . '/app', 'RoleAdminController.php'),
    ['class RoleAdminController', 'function index'],
);

$controllerConfigs = $findFiles($root . '/app', 'controllers.php');
$configSources = [];
foreach ($controllerConfigs as $path) {
    $configSources[$path] = (string) file_get_contents($path);
}

foreach (['UserAdminController', 'RoleAdminController'] as $controller) {
    $owners = [];
    foreach ($configSources as $path => $source) {
        if (str_contains($source, $controller)) {
            $owners[] = ltrim(str_replace($root, '', $path), '/');
        }
    }

    if ($owners === []) {
        $errors[] = "No controller factory references {$controller}.";
    }
}

echo 'AUTH_GRID_CUTOVER_READINESS_ERRORS ' . count($errors) . PHP_EOL;
if ($userController !== null) {
    echo 'USER_CONTROLLER ' . ltrim(str_replace($root, '', $userController), '/') . PHP_EOL;
}
if ($roleController !== null) {
    echo 'ROLE_CONTROLLER ' . ltrim(str_replace($root, '', $roleController), '/') . PHP_EOL;
}
foreach ($errors as $error) {
    echo '- ' . $error . PHP_EOL;
}

exit($errors === [] ? 0 : 1);
