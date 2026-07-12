<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin user locale hydration verification\n";
print "================================================\n\n";

$modelPath = find_admin_user_model($basePath);
$hydrationPath = find_admin_user_hydration($basePath, $modelPath);
$model = $modelPath !== null ? (string) file_get_contents($modelPath) : '';
$hydration = $hydrationPath !== null ? (string) file_get_contents($hydrationPath) : '';

$provider = new \Zoosper\Core\I18n\I18nServiceProvider($basePath, ['admin_locale' => 'en_AU', 'fallback_locale' => 'en_AU']);
$services = new \Zoosper\Core\Container\ServiceContainer();
$provider->register($services);
$resolver = $services->get(\Zoosper\Core\I18n\AdminUserLocaleResolver::class);
$user = new class {
    public ?string $locale = 'en_GB';
};

$checks = [
    'concrete AdminUser model found' => $modelPath !== null,
    'AdminUser model is not AdminUserLocaleResolver' => $modelPath !== null && !str_contains($modelPath, 'AdminUserLocaleResolver'),
    'AdminUser model exposes locale constructor/property' => preg_match('/\$locale\b/', $model) === 1,
    'AdminUser hydration file found' => $hydrationPath !== null,
    'AdminUser hydration references locale row value' => str_contains($hydration, "['locale']") || str_contains($hydration, 'locale:'),
    'I18nServiceProvider registers AdminUserLocaleResolver' => $services->has(\Zoosper\Core\I18n\AdminUserLocaleResolver::class),
    'AdminUserLocaleResolver resolves from container' => $resolver instanceof \Zoosper\Core\I18n\AdminUserLocaleResolver,
    'AdminUserLocaleResolver uses user locale when valid' => $resolver instanceof \Zoosper\Core\I18n\AdminUserLocaleResolver && $resolver->resolveForAdminUser($user)->activeLocale === 'en_GB',
    'AdminUserLocaleResolver rejects unsafe locale' => $resolver instanceof \Zoosper\Core\I18n\AdminUserLocaleResolver && !$resolver->isValidLocale('../bad'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nModel: " . ($modelPath ?? 'not found') . PHP_EOL;
print "Hydration: " . ($hydrationPath ?? 'not found') . PHP_EOL;
print "Result: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);

function find_admin_user_model(string $basePath): ?string
{
    foreach (php_files($basePath) as $path) {
        $contents = (string) file_get_contents($path);
        if (basename($path) === 'AdminUser.php'
            && preg_match('/\bclass\s+AdminUser\b/', $contents) === 1
            && !str_contains($contents, 'class AdminUserLocaleResolver')) {
            return $path;
        }
    }

    return null;
}

function find_admin_user_hydration(string $basePath, ?string $modelPath): ?string
{
    $matches = [];
    foreach (php_files($basePath) as $path) {
        if ($modelPath !== null && $path === $modelPath) {
            continue;
        }

        $contents = (string) file_get_contents($path);
        if (str_contains($contents, 'new AdminUser(') && !str_contains($contents, 'class AdminUserLocaleResolver')) {
            $matches[] = $path;
        }
    }

    usort($matches, static function (string $a, string $b): int {
        $score = static fn (string $path): int => str_contains($path, 'Repository') ? 0 : (str_contains($path, 'Service') ? 1 : 2);

        return $score($a) <=> $score($b) ?: strcmp($a, $b);
    });

    return $matches[0] ?? null;
}

/** @return list<string> */
function php_files(string $basePath): array
{
    $results = [];
    foreach ([$basePath . '/app', $basePath . '/modules'] as $root) {
        if (!is_dir($root)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->getExtension() === 'php') {
                $results[] = $file->getPathname();
            }
        }
    }

    return $results;
}
