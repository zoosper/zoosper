<?php

declare(strict_types=1);

$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require $composerAutoload;
    return;
}

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Zoosper\\Core\\' => dirname(__DIR__) . '/app/zoosper-core/src/',
        'Zoosper\\Api\\' => dirname(__DIR__) . '/app/zoosper-api/src/',
        'Zoosper\\Admin\\' => dirname(__DIR__) . '/app/zoosper-admin/src/',
        'Zoosper\\Auth\\' => dirname(__DIR__) . '/app/zoosper-auth/src/',
        'Zoosper\\Site\\' => dirname(__DIR__) . '/app/zoosper-site/src/',
        'Zoosper\\Page\\' => dirname(__DIR__) . '/app/zoosper-page/src/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

        if (is_file($file)) {
            require $file;
        }
    }
});
