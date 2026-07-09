<?php

declare(strict_types=1);

$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';

if (is_file($composerAutoload)) {
    require $composerAutoload;
} else {
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

            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (is_file($file)) {
                require $file;
            }
        }
    });
}

function env(string $key, mixed $default = null): mixed
{
    static $loaded = false;

    if (!$loaded) {
        $envFile = dirname(__DIR__) . '/.env';

        if (is_file($envFile)) {
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                $line = trim($line);

                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }

                [$name, $value] = explode('=', $line, 2);
                $_ENV[trim($name)] = trim($value, " \t\n\r\0\x0B\"'");
            }
        }

        $loaded = true;
    }

    return $_ENV[$key] ?? getenv($key) ?: $default;
}
