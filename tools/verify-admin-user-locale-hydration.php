<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin user locale hydration verification\n";
print "================================================\n\n";

$modelPath = find_file_containing($basePath, 'class AdminUser');
$hydrationPath = find_file_containing($basePath, 'new AdminUser(');
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
    'AdminUser model found' => $modelPath !== null,
    'AdminUser model exposes locale constructor/property' => str_contains($model, '$locale'),
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

function find_file_containing(string $basePath, string $needle): ?string
{
    foreach ([$basePath . '/app', $basePath . '/modules'] as $root) {
        if (!is_dir($root)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            $path = $file->getPathname();
            if (str_contains((string) file_get_contents($path), $needle)) {
                return $path;
            }
        }
    }

    return null;
}
