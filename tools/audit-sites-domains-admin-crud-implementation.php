<?php

declare(strict_types=1);

$root = dirname(__DIR__);

print "Zoosper sites/domains admin CRUD implementation audit\n";
print "=====================================================\n\n";

$sourceFiles = collectSourceFiles($root);
$source = '';
foreach ($sourceFiles as $file) {
    $source .= "\n/* FILE: " . str_replace($root . '/', '', $file) . " */\n" . (string) file_get_contents($file);
}

$requiredRoutes = [
    '/admin/sites',
    '/admin/sites/create',
    '/admin/sites/edit',
    '/admin/site-domains',
    '/admin/site-domains/create',
    '/admin/site-domains/edit',
];

$checks = [];
foreach ($requiredRoutes as $route) {
    $checks['route target present: ' . $route] = str_contains($source, $route);
}

$checks += [
    'site admin controller implementation exists' => containsAny($source, ['class SiteAdminController', 'class SitesAdminController']),
    'site domains admin controller implementation exists' => containsAny($source, ['class SiteDomainAdminController', 'class SiteDomainsAdminController']),
    'site save/create handling exists' => containsAny($source, ['createSite', 'saveSite', 'SiteRepository::create', '->createSite', '->saveSite']),
    'site domain save/create handling exists' => containsAny($source, ['createDomain', 'saveDomain', 'SiteDomainRepository::create', '->createDomain', '->saveDomain']),
    'site manage permission referenced' => str_contains($source, 'site.manage'),
    'settings manage fallback referenced' => str_contains($source, 'settings.manage'),
    'route readiness docs preserve deferred list' => is_file($root . '/docs/roadmap/deferred-near-term.md'),
];

$missing = [];
foreach ($checks as $label => $ok) {
    print '- ' . $label . ': ' . ($ok ? 'ok' : 'MISSING') . PHP_EOL;
    if (!$ok) {
        $missing[] = $label;
    }
}

print "\nResult: " . ($missing === [] ? 'OK' : 'NEEDS_IMPLEMENTATION') . PHP_EOL;

if ($missing !== []) {
    print "\nMissing implementation signals:\n";
    foreach ($missing as $item) {
        print '- ' . $item . PHP_EOL;
    }
}

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
