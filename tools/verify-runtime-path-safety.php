<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$htmlConfig = is_file($basePath . '/config/html_sanitizer.php') ? require $basePath . '/config/html_sanitizer.php' : [];
$templateConfig = is_file($basePath . '/config/template.php') ? require $basePath . '/config/template.php' : [];

$htmlCache = resolveRuntimePath($basePath, (string) ($htmlConfig['cache_path'] ?? 'var/cache/htmlpurifier'));
$templateCache = resolveRuntimePath($basePath, (string) ($templateConfig['template_cache_path'] ?? 'var/cache/templates'));

print "Zoosper runtime path safety verification\n";
print "========================================\n\n";

$checks = [
    'html cache under project var' => str_starts_with($htmlCache, $basePath . '/var/'),
    'html cache not under public' => !str_starts_with($htmlCache, $basePath . '/public/'),
    'template cache under project var' => str_starts_with($templateCache, $basePath . '/var/'),
    'template cache not under public' => !str_starts_with($templateCache, $basePath . '/public/'),
    'public/var absent' => !file_exists($basePath . '/public/var'),
    'public/storage absent' => !file_exists($basePath . '/public/storage'),
    'public/cache absent' => !file_exists($basePath . '/public/cache'),
    'public/tmp absent' => !file_exists($basePath . '/public/tmp'),
    'ProjectPathResolver exists' => class_exists(\Zoosper\Core\Filesystem\ProjectPathResolver::class),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResolved paths:\n";
print '- html_cache    : ' . $htmlCache . PHP_EOL;
print '- template_cache: ' . $templateCache . PHP_EOL;
print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);

function resolveRuntimePath(string $basePath, string $path): string
{
    if (str_starts_with($path, '/')) {
        return rtrim($path, '/');
    }

    $path = trim($path, '/');
    if (str_starts_with($path, 'var/')) {
        return $basePath . '/' . $path;
    }

    return $basePath . '/var/' . $path;
}
