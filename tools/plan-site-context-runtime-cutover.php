<?php

declare(strict_types=1);

/**
 * Site context runtime cutover planner.
 *
 * Read-only planner that inspects the live resolver/factory shape and writes a
 * human-reviewed patch draft for replacing SiteRepository/DbSite imports with
 * the core-owned SiteLookupInterface/ResolvedSite boundary.
 */

$root = dirname(__DIR__);
$reportDir = $root . '/var/reports';
$errors = [];
$warnings = [];
$observations = [];
$findings = [];

$requiredFoundation = [
    'app/zoosper-core/src/Site/SiteLookupInterface.php',
    'app/zoosper-core/src/Site/ResolvedSite.php',
    'app/zoosper-core/src/Site/NullSiteLookup.php',
    'app/zoosper-site/src/Infrastructure/DatabaseSiteLookup.php',
];

foreach ($requiredFoundation as $file) {
    if (!is_file($root . '/' . $file)) {
        $errors[] = 'Missing required Site lookup boundary foundation file: ' . $file;
    }
}

$targets = [
    'SiteContextResolver' => 'app/zoosper-core/src/Site/SiteContextResolver.php',
    'SiteContextResolverFactory' => 'app/zoosper-core/src/Site/SiteContextResolverFactory.php',
];

foreach ($targets as $label => $relative) {
    $path = $root . '/' . $relative;
    if (!is_file($path)) {
        $errors[] = 'Missing target file: ' . $relative;
        continue;
    }

    $source = (string) file_get_contents($path);
    $lines = preg_split('/\R/', $source) ?: [];

    $finding = [
        'siteNamespaceReferences' => [],
        'siteRepositoryTokens' => [],
        'dbSiteTokens' => [],
        'constructorLines' => [],
        'methodSignatures' => [],
        'hasSiteLookupInterface' => str_contains($source, 'SiteLookupInterface'),
        'hasResolvedSite' => str_contains($source, 'ResolvedSite'),
        'hasNullSiteLookup' => str_contains($source, 'NullSiteLookup'),
    ];

    foreach ($lines as $index => $line) {
        $trimmed = trim($line);
        if (str_contains($line, 'Zoosper\\Site\\')) {
            $finding['siteNamespaceReferences'][] = ['line' => $index + 1, 'source' => $trimmed];
        }
        if (str_contains($line, 'SiteRepository')) {
            $finding['siteRepositoryTokens'][] = ['line' => $index + 1, 'source' => $trimmed];
        }
        if (str_contains($line, 'DbSite')) {
            $finding['dbSiteTokens'][] = ['line' => $index + 1, 'source' => $trimmed];
        }
        if (str_contains($line, '__construct(') || str_contains($line, 'private function contextFromDbSite') || str_contains($line, 'private function dbSiteMatchesPath')) {
            $finding['methodSignatures'][] = ['line' => $index + 1, 'source' => $trimmed];
        }
        if (str_contains($line, 'private ?SiteRepository') || str_contains($line, 'SiteRepository $')) {
            $finding['constructorLines'][] = ['line' => $index + 1, 'source' => $trimmed];
        }
    }

    if ($finding['siteNamespaceReferences'] !== []) {
        $warnings[] = $relative . ' still has direct Site module references: ' . count($finding['siteNamespaceReferences']);
    }

    $findings[$relative] = $finding;
}

$draft = buildDraft($findings);

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/site-context-runtime-cutover-draft.patch.md', $draft);

$observations[] = 'Prepared a human-reviewed SiteContextResolver/SiteContextResolverFactory cutover draft.';
$observations[] = 'This phase does not edit runtime source files.';

$payload = [
    'generatedAt' => gmdate('c'),
    'errors' => $errors,
    'warnings' => $warnings,
    'observations' => $observations,
    'findings' => $findings,
    'draft' => 'var/reports/site-context-runtime-cutover-draft.patch.md',
];

$report = [];
$report[] = '## Site Context Runtime Cutover Plan';
$report[] = '';
$report[] = 'Generated: ' . $payload['generatedAt'];
$report[] = '';
$report[] = 'Errors: ' . count($errors);
$report[] = 'Warnings: ' . count($warnings);
$report[] = 'Observations: ' . count($observations);
$report[] = '';
$report[] = '### Warnings';
foreach ($warnings as $warning) {
    $report[] = '- ' . $warning;
}
$report[] = '';
$report[] = '### Observations';
foreach ($observations as $observation) {
    $report[] = '- ' . $observation;
}
$report[] = '';
$report[] = '### Target findings';
foreach ($findings as $file => $finding) {
    $report[] = '#### ' . $file;
    $report[] = '- Site namespace references: ' . count($finding['siteNamespaceReferences']);
    $report[] = '- SiteRepository tokens: ' . count($finding['siteRepositoryTokens']);
    $report[] = '- DbSite tokens: ' . count($finding['dbSiteTokens']);
    $report[] = '- Already references SiteLookupInterface: ' . ($finding['hasSiteLookupInterface'] ? 'yes' : 'no');
    foreach ($finding['siteNamespaceReferences'] as $item) {
        $report[] = '  - line ' . $item['line'] . ': ' . $item['source'];
    }
}
$report[] = '';
$report[] = '### Draft';
$report[] = '- See var/reports/site-context-runtime-cutover-draft.patch.md';

if ($errors !== []) {
    $report[] = '';
    $report[] = '### Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($reportDir . '/site-context-runtime-cutover-plan.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/site-context-runtime-cutover-plan.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);

function buildDraft(array $findings): string
{
    $lines = [];
    $lines[] = '# Site context runtime cutover patch draft';
    $lines[] = '';
    $lines[] = 'This is a human-reviewed draft. It is not automatically applied.';
    $lines[] = '';
    $lines[] = '## Intended core imports';
    $lines[] = '';
    $lines[] = 'Add to resolver/factory as needed:';
    $lines[] = '';
    $lines[] = '```php';
    $lines[] = 'use Zoosper\\Core\\Site\\NullSiteLookup;';
    $lines[] = 'use Zoosper\\Core\\Site\\ResolvedSite;';
    $lines[] = 'use Zoosper\\Core\\Site\\SiteLookupInterface;';
    $lines[] = '```';
    $lines[] = '';
    $lines[] = 'Remove from core resolver/factory:';
    $lines[] = '';
    $lines[] = '```php';
    $lines[] = 'use Zoosper\\Site\\Model\\Site as DbSite;';
    $lines[] = 'use Zoosper\\Site\\Repository\\SiteRepository;';
    $lines[] = '```';
    $lines[] = '';
    $lines[] = '## Intended constructor shape';
    $lines[] = '';
    $lines[] = '```php';
    $lines[] = 'public function __construct(';
    $lines[] = '    private ?SiteLookupInterface $sites = null,';
    $lines[] = ') {';
    $lines[] = '    $this->sites ??= new NullSiteLookup();';
    $lines[] = '}';
    $lines[] = '```';
    $lines[] = '';
    $lines[] = '## Intended resolver method shape';
    $lines[] = '';
    $lines[] = '- Replace `contextFromDbSite(DbSite $site)` with `contextFromResolvedSite(ResolvedSite $site)`.';
    $lines[] = '- Replace `dbSiteMatchesPath(DbSite $site, string $path)` with a ResolvedSite-compatible helper or move path matching into the Site module adapter.';
    $lines[] = '- Preserve existing siteId population in SiteContext.';
    $lines[] = '- Preserve request isolation semantics.';
    $lines[] = '';
    $lines[] = '## Live findings';
    foreach ($findings as $file => $finding) {
        $lines[] = '';
        $lines[] = '### ' . $file;
        foreach ($finding['siteNamespaceReferences'] as $item) {
            $lines[] = '- line ' . $item['line'] . ': `' . $item['source'] . '`';
        }
    }

    return implode("\n", $lines) . "\n";
}
