<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin notice-error selector alias hotfix\n";
print "================================================\n\n";

$target = find_notice_css_target($basePath);
if ($target === null) {
    fwrite(STDERR, "Unable to locate an admin CSS file for notice styling.\n");
    exit(2);
}

$css = (string) file_get_contents($target);
if (str_contains($css, '.notice-error') || str_contains($css, '.notice.notice-error')) {
    print '- notice-error selector already exists in ' . relative_path($basePath, $target) . PHP_EOL;
    print "Result: OK\n";
    exit(0);
}

$block = <<<'CSS'

/* Phase 1.19.2: standard error notice selector alias. */
.notice.notice-error,
.notice-error {
    background: #fef3f2;
    border: 1px solid #fecdca;
    color: #b42318;
}
CSS;

$backup = $target . '.phase-1.19.2.bak';
if (!is_file($backup)) {
    copy($target, $backup);
    print '- backup created: ' . relative_path($basePath, $backup) . PHP_EOL;
}

file_put_contents($target, rtrim($css) . PHP_EOL . $block . PHP_EOL);
print '- updated ' . relative_path($basePath, $target) . PHP_EOL;
print "Result: OK\n";

function find_notice_css_target(string $basePath): ?string
{
    $preferred = [
        $basePath . '/public/assets/admin/css/zoosper-admin-messages.css',
        $basePath . '/public/assets/admin/css/admin.css',
        $basePath . '/public/admin/css/admin.css',
    ];

    foreach ($preferred as $path) {
        if (is_file($path)) {
            return $path;
        }
    }

    foreach ([$basePath . '/public', $basePath . '/assets', $basePath . '/app'] as $root) {
        if (!is_dir($root)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->getExtension() === 'css') {
                $path = $file->getPathname();
                $contents = (string) file_get_contents($path);
                if (str_contains($path, 'admin') || str_contains($contents, '.notice')) {
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
