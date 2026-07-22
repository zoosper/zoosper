<?php

declare(strict_types=1);

$root = dirname(__DIR__);

print "Zoosper sites and site domains admin CRUD audit\n";
print "================================================\n\n";

$sourceFiles = collectSourceFiles($root);
$all = '';
foreach ($sourceFiles as $file) {
    $all .= "\n/* FILE: " . str_replace($root . '/', '', $file) . " */\n" . (string) file_get_contents($file);
}

$checks = [
    'sites admin route target is documented or implemented' => str_contains($all, '/admin/sites'),
    'site domains admin route target is documented or implemented' => str_contains($all, '/admin/site-domains'),
    'site manager/admin CRUD vocabulary exists' => containsAny($all, ['SiteAdminController', 'SitesAdminController', 'SiteRepository', 'site.manage', 'sites admin']),
    'site domain CRUD vocabulary exists' => containsAny($all, ['SiteDomain', 'SiteDomainRepository', 'SiteDomainAdminController', 'site domain']),
    'admin CRUD phase documentation exists' => is_file($root . '/docs/roadmap/phase-1.37v-sites-and-site-domains-admin-crud.md'),
    'operations documentation exists' => is_file($root . '/docs/operations/sites-and-site-domains-admin-crud.md'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'MISSING') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nRecommended CRUD endpoints:\n";
foreach ([
    '/admin/sites',
    '/admin/sites/create',
    '/admin/sites/edit',
    '/admin/site-domains',
    '/admin/site-domains/create',
    '/admin/site-domains/edit',
] as $endpoint) {
    print '- ' . $endpoint . PHP_EOL;
}

print "\nResult: " . ($failed ? 'NEEDS_IMPLEMENTATION' : 'OK') . PHP_EOL;
exit($failed ? 0 : 0);

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
