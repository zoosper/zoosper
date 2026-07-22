<?php

declare(strict_types=1);

$root = dirname(__DIR__);

print "Zoosper sites and site domains admin CRUD bulk audit\n";
print "====================================================\n\n";

$sourceFiles = collectSourceFiles($root);
$source = '';
foreach ($sourceFiles as $file) {
    $source .= "\n/* FILE: " . str_replace($root . '/', '', $file) . " */\n" . (string) file_get_contents($file);
}

$checks = [
    'sites route target exists in source/docs' => str_contains($source, '/admin/sites'),
    'site domains route target exists in source/docs' => str_contains($source, '/admin/site-domains'),
    'sites create/edit targets exist in source/docs' => str_contains($source, '/admin/sites/create') && str_contains($source, '/admin/sites/edit'),
    'site domains create/edit targets exist in source/docs' => str_contains($source, '/admin/site-domains/create') && str_contains($source, '/admin/site-domains/edit'),
    'site admin CRUD implementation vocabulary exists' => containsAny($source, ['SiteAdminController', 'SitesAdminController', 'SiteForm', 'site.manage', 'Sites admin CRUD']),
    'site domain admin CRUD implementation vocabulary exists' => containsAny($source, ['SiteDomainAdminController', 'SiteDomainRepository', 'SiteDomainForm', 'Site Domains admin CRUD']),
    'bulk implementation docs exist' => is_file($root . '/docs/roadmap/phase-1.37v-bulk-sites-domains-admin-crud.md'),
    'source inspection helper exists' => is_file($root . '/tools/inspect-sites-domains-admin-crud-bulk.php'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'MISSING') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nTarget CRUD routes:\n";
foreach ([
    '/admin/sites',
    '/admin/sites/create',
    '/admin/sites/edit',
    '/admin/site-domains',
    '/admin/site-domains/create',
    '/admin/site-domains/edit',
] as $route) {
    print '- ' . $route . PHP_EOL;
}

print "\nResult: " . ($failed ? 'NEEDS_IMPLEMENTATION' : 'OK') . PHP_EOL;
exit(0);

/** @return list<string> */
function collectSourceFiles(string $root): array
{
    $roots = ['app', 'packages', 'themes', 'resources', 'templates', 'docs'];
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
            if (in_array(strtolower($file->getExtension()), $extensions, true)) {
                $files[] = $file->getPathname();
            }
        }
    }

    sort($files);
    return $files;
}

function containsAny(string $haystack, array $needles): bool
{
    foreach ($needles as $needle) {
        if (str_contains($haystack, $needle)) {
            return true;
        }
    }

    return false;
}
