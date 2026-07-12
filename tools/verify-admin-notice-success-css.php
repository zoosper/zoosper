<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin notice success CSS verification\n";
print "=============================================\n\n";

$cssFiles = find_admin_css_files($basePath);
$combined = '';
foreach ($cssFiles as $file) {
    $combined .= "\n/* {$file} */\n" . (string) file_get_contents($file);
}

$checks = [
    'admin CSS file found' => $cssFiles !== [],
    'notice-success selector exists' => str_contains($combined, '.notice-success') || str_contains($combined, '.notice.notice-success'),
    'success notice has green-ish background' => preg_match('/\.notice(?:\.notice-success|-success)[^{]*\{[^}]*background\s*:\s*#(?:ecfdf3|d1fae5|dcfce7|f0fdf4)/is', $combined) === 1 || str_contains($combined, '#ecfdf3'),
    'success notice has border styling' => str_contains($combined, 'border:') && (str_contains($combined, '#abefc6') || str_contains($combined, '#bbf7d0') || str_contains($combined, '#86efac')),
    'success notice has readable text colour' => str_contains($combined, '#067647') || str_contains($combined, '#166534') || str_contains($combined, '#047857'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nCSS files considered:\n";
foreach ($cssFiles as $file) {
    print '- ' . relative_path($basePath, $file) . PHP_EOL;
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
                if (str_contains($path, '/admin/') || str_contains($path, 'admin')) {
                    $files[] = $path;
                }
            }
        }
    }
    return array_values(array_unique($files));
}

function relative_path(string $basePath, string $path): string
{
    return str_starts_with($path, $basePath . '/') ? substr($path, strlen($basePath) + 1) : $path;
}
