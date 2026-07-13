<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin notice error CSS verification\n";
print "===========================================\n\n";

$css = '';
foreach (find_admin_css_files($basePath) as $file) {
    $css .= "\n/* {$file} */\n" . (string) file_get_contents($file);
}

$checks = [
    'notice-error selector exists' => str_contains($css, '.notice-error') || str_contains($css, '.notice.notice-error'),
    'error notice has red-ish background' => str_contains($css, '#fef3f2') || str_contains($css, '#fee2e2') || str_contains($css, '#fff1f2') || str_contains($css, '#fef2f2'),
    'error notice has border styling' => str_contains($css, '#fecdca') || str_contains($css, '#fecaca') || str_contains($css, '#fca5a5') || str_contains($css, 'border:'),
    'error notice has readable text colour' => str_contains($css, '#b42318') || str_contains($css, '#991b1b') || str_contains($css, '#b91c1c') || str_contains($css, '#dc2626'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);

/** @return list<string> */
function find_admin_css_files(string $basePath): array
{
    $files = [];
    foreach ([$basePath . '/public', $basePath . '/assets', $basePath . '/app'] as $root) {
        if (!is_dir($root)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->getExtension() === 'css') {
                $path = $file->getPathname();
                if (str_contains($path, 'admin') || str_contains((string) file_get_contents($path), '.notice')) {
                    $files[] = $path;
                }
            }
        }
    }

    return array_values(array_unique($files));
}
