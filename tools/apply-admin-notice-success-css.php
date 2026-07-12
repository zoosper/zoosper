<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin notice success CSS restoration\n";
print "===========================================\n\n";

$target = find_notice_css_target($basePath);
if ($target === null) {
    fwrite(STDERR, "Unable to locate an admin CSS file. Expected admin.css or zoosper-admin-messages.css.\n");
    exit(2);
}

$css = (string) file_get_contents($target);
if (str_contains($css, '.notice-success')) {
    print '- notice success CSS already exists in ' . relative_path($basePath, $target) . PHP_EOL;
    print "Result: OK\n";
    exit(0);
}

$block = <<<'CSS'

/* Phase 1.10: restore visible admin notice styling. */
.notice {
    border-radius: 8px;
    margin: 0 0 16px;
    padding: 12px 14px;
}

.notice.notice-success,
.notice-success {
    background: #ecfdf3;
    border: 1px solid #abefc6;
    color: #067647;
}
CSS;

$backup = $target . '.phase-1.10.bak';
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
        $basePath . '/assets/admin/css/zoosper-admin-messages.css',
        $basePath . '/assets/admin/css/admin.css',
        $basePath . '/app/zoosper-admin/public/assets/admin/css/zoosper-admin-messages.css',
        $basePath . '/app/zoosper-admin/public/assets/admin/css/admin.css',
    ];

    foreach ($preferred as $path) {
        if (is_file($path)) {
            return $path;
        }
    }

    $matches = [];
    foreach ([$basePath . '/public', $basePath . '/assets', $basePath . '/app'] as $root) {
        if (!is_dir($root)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->getExtension() === 'css') {
                $path = $file->getPathname();
                if (str_contains($path, '/admin/') || str_contains($path, 'admin')) {
                    $matches[] = $path;
                }
            }
        }
    }

    usort($matches, static function (string $a, string $b): int {
        $score = static fn (string $path): int => str_contains($path, 'zoosper-admin-messages') ? 0 : (str_contains($path, 'admin.css') ? 1 : 2);
        return $score($a) <=> $score($b) ?: strcmp($a, $b);
    });

    return $matches[0] ?? null;
}

function relative_path(string $basePath, string $path): string
{
    return str_starts_with($path, $basePath . '/') ? substr($path, strlen($basePath) + 1) : $path;
}
