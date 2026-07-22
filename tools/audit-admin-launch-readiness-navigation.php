<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$targets = [
    'Site Domains' => '/admin/site-domains',
    'Sites' => '/admin/sites',
    'Settings' => '/admin/settings',
];

print "Zoosper admin launch readiness navigation audit\n";
print "================================================\n\n";

$sourceFiles = collectSourceFiles($root);
$allSource = '';
foreach ($sourceFiles as $file) {
    $allSource .= "\n/* FILE: " . str_replace($root . '/', '', $file) . " */\n" . (string) file_get_contents($file);
}

$failed = false;
foreach ($targets as $label => $path) {
    $deadPattern = '~<a\s+[^>]*href=["\']\#["\'][^>]*>\s*' . preg_quote($label, '~') . '\s*</a>~i';
    $hasDeadLink = preg_match($deadPattern, $allSource) === 1;
    $hasTarget = str_contains($allSource, $path);

    print '- ' . $label . ' dead href="#": ' . ($hasDeadLink ? 'FAIL' : 'ok') . PHP_EOL;
    print '- ' . $label . ' target ' . $path . ': ' . ($hasTarget ? 'ok' : 'MISSING') . PHP_EOL;

    $failed = $failed || $hasDeadLink || !$hasTarget;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
if ($failed) {
    print "\nNext: run tools/apply-admin-launch-readiness-navigation.php --write after reviewing dry-run output.\n";
}

exit($failed ? 2 : 0);

/** @return list<string> */
function collectSourceFiles(string $root): array
{
    $roots = ['app', 'packages', 'themes', 'resources', 'templates'];
    $extensions = ['php', 'phtml', 'latte', 'html', 'md'];
    $files = [];

    foreach ($roots as $relativeRoot) {
        $dir = $root . '/' . $relativeRoot;
        if (!is_dir($dir)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $extension = strtolower($file->getExtension());
            if (in_array($extension, $extensions, true)) {
                $files[] = $file->getPathname();
            }
        }
    }

    sort($files);
    return $files;
}
