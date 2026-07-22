<?php

declare(strict_types=1);

$root = dirname(__DIR__);

print "Zoosper Sites/Site Domains admin CRUD runtime audit\n";
print "====================================================\n\n";

$source = collectSource($root);
$checks = [
    'Sites route target present' => str_contains($source, '/admin/sites'),
    'Sites create target present' => str_contains($source, '/admin/sites/create'),
    'Sites edit target present' => str_contains($source, '/admin/sites/edit'),
    'Site Domains route target present' => str_contains($source, '/admin/site-domains'),
    'Site Domains create target present' => str_contains($source, '/admin/site-domains/create'),
    'Site Domains edit target present' => str_contains($source, '/admin/site-domains/edit'),
    'Site admin controller class present' => containsAny($source, ['class SiteAdminController', 'class SitesAdminController']),
    'Site domain admin controller class present' => containsAny($source, ['class SiteDomainAdminController', 'class SiteDomainsAdminController']),
    'Site domain repository or model present' => containsAny($source, ['class SiteDomainRepository', 'class SiteDomain']),
    'Site manage permission present' => str_contains($source, 'site.manage'),
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
    print "\nMissing runtime signals:\n";
    foreach ($missing as $item) {
        print '- ' . $item . PHP_EOL;
    }
    print "\nRun tools/prepare-sites-domains-admin-crud-runtime.php to inspect supported write targets.\n";
}

exit(0);

function collectSource(string $root): string
{
    $roots = ['app', 'packages', 'docs'];
    $extensions = ['php', 'latte', 'phtml', 'html', 'md'];
    $buffer = '';
    foreach ($roots as $relativeRoot) {
        $dir = $root . '/' . $relativeRoot;
        if (!is_dir($dir)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile() || !in_array(strtolower($file->getExtension()), $extensions, true)) {
                continue;
            }
            $buffer .= "\n/* FILE: " . str_replace($root . '/', '', $file->getPathname()) . " */\n" . (string) file_get_contents($file->getPathname());
        }
    }
    return $buffer;
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
