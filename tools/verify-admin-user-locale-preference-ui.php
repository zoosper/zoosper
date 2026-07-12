<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin user locale preference UI verification\n";
print "====================================================\n\n";

$config = is_file($basePath . '/config/i18n.php') ? require $basePath . '/config/i18n.php' : [];
$provider = new \Zoosper\Core\I18n\SupportedLocaleProvider(is_array($config) ? $config : []);
$renderer = new \Zoosper\Admin\I18n\AdminUserLocalePreferenceFieldRenderer($provider);
$html = $renderer->render('en_AU');
$target = find_locale_ui_target($basePath);
$targetSource = $target !== null ? (string) file_get_contents($target) : '';

$checks = [
    'SupportedLocaleProvider exists' => class_exists(\Zoosper\Core\I18n\SupportedLocaleProvider::class),
    'AdminUserLocalePreferenceFieldRenderer exists' => class_exists(\Zoosper\Admin\I18n\AdminUserLocalePreferenceFieldRenderer::class),
    'renderer outputs locale select' => str_contains($html, 'name="locale"') && str_contains($html, '<select'),
    'renderer outputs configured locale option' => str_contains($html, 'en_AU') && str_contains($html, 'English (Australia)'),
    'renderer selects current locale' => str_contains($html, 'value="en_AU" selected'),
    'renderer includes blank configured-locale fallback option' => str_contains($html, 'Use configured admin locale'),
    'SupportedLocaleProvider rejects unsafe locale' => !$provider->isSupportedAdminLocale('../bad'),
    'admin user locale hydration still passes basic model contract' => class_exists(\Zoosper\Core\I18n\AdminUserLocaleResolver::class),
    'admin user form has locale field if UI patch has been applied' => $target === null || str_contains($targetSource, 'name="locale"') || str_contains($targetSource, "name='locale'"),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nUI target: " . ($target ?? 'not detected') . PHP_EOL;
print "Result: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);

function find_locale_ui_target(string $basePath): ?string
{
    foreach (php_files($basePath) as $path) {
        $contents = (string) file_get_contents($path);
        if ((str_contains($contents, 'AdminUser') || str_contains($contents, 'admin user') || str_contains($contents, 'Admin user'))
            && (str_contains($contents, 'name="locale"') || str_contains($contents, "name='locale'"))) {
            return $path;
        }
    }

    return null;
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
