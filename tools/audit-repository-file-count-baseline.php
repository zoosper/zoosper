<?php

declare(strict_types=1);

/**
 * Repository file-count baseline audit.
 *
 * This read-only audit records source/documentation/tool/test file counts so
 * future phases can detect file bloat early. It intentionally excludes generated
 * and transient folders such as vendor, var, .git, cache, reports and quarantine.
 */

$root = dirname(__DIR__);
$writeBaseline = in_array('--write-baseline', $argv, true);
$reportDir = $root . '/var/reports';
$baselineDir = $root . '/docs/metrics';
$baselinePath = $baselineDir . '/repository-file-count-baseline.json';

$excludedPrefixes = [
    '.git/',
    'vendor/',
    'var/',
    'node_modules/',
    '.idea/',
    '.vscode/',
    'storage/cache/',
    'tmp/',
];

$groups = [
    'app_php' => static fn (string $path): bool => str_starts_with($path, 'app/') && str_ends_with($path, '.php'),
    'app_tests' => static fn (string $path): bool => str_starts_with($path, 'app/') && str_contains($path, '/tests/') && str_ends_with($path, '.php'),
    'tools_php' => static fn (string $path): bool => str_starts_with($path, 'tools/') && str_ends_with($path, '.php'),
    'docs_markdown' => static fn (string $path): bool => str_starts_with($path, 'docs/') && preg_match('/\.(md|txt)$/i', $path) === 1,
    'config_php' => static fn (string $path): bool => str_starts_with($path, 'app/') && str_contains($path, '/config/') && str_ends_with($path, '.php'),
    'templates' => static fn (string $path): bool => preg_match('/\.(latte|phtml|html|twig)$/i', $path) === 1,
];

$files = listProjectFiles($root, $excludedPrefixes);
$counts = [
    'total_tracked_candidate_files' => count($files),
];

foreach ($groups as $name => $matcher) {
    $counts[$name] = count(array_filter(array_keys($files), $matcher));
}

$pageMomentumActive = array_values(array_filter(
    array_keys($files),
    static fn (string $path): bool => preg_match('/page-admin-momentum|page-momentum|pagemomentum|page_momentum/i', $path) === 1,
));
$counts['page_momentum_active_files'] = count($pageMomentumActive);

$previous = null;
$delta = null;
if (is_file($baselinePath)) {
    $decoded = json_decode((string) file_get_contents($baselinePath), true);
    if (is_array($decoded) && isset($decoded['counts']) && is_array($decoded['counts'])) {
        $previous = $decoded['counts'];
        $delta = [];
        foreach ($counts as $key => $value) {
            $delta[$key] = $value - (int) ($previous[$key] ?? 0);
        }
    }
}

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$payload = [
    'generatedAt' => gmdate('c'),
    'counts' => $counts,
    'previousCounts' => $previous,
    'delta' => $delta,
    'pageMomentumActiveFiles' => $pageMomentumActive,
    'excludedPrefixes' => $excludedPrefixes,
];

if ($writeBaseline) {
    if (!is_dir($baselineDir)) {
        mkdir($baselineDir, 0775, true);
    }
    file_put_contents($baselinePath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

$report = [];
$report[] = '## Repository File Count Baseline Audit';
$report[] = '';
$report[] = 'Generated: ' . $payload['generatedAt'];
$report[] = 'Baseline written: ' . ($writeBaseline ? 'yes' : 'no');
$report[] = '';
$report[] = '### Counts';
foreach ($counts as $key => $value) {
    $line = '- ' . $key . ': ' . $value;
    if (is_array($delta) && array_key_exists($key, $delta)) {
        $line .= ' (delta ' . ($delta[$key] >= 0 ? '+' : '') . $delta[$key] . ')';
    }
    $report[] = $line;
}

$report[] = '';
$report[] = '### Active Page Momentum files';
foreach ($pageMomentumActive as $file) {
    $report[] = '- ' . $file;
}

file_put_contents($reportDir . '/repository-file-count-baseline.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/repository-file-count-baseline.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit(0);

/**
 * @param list<string> $excludedPrefixes
 * @return array<string,string>
 */
function listProjectFiles(string $root, array $excludedPrefixes): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        $path = $file->getPathname();
        $relative = str_replace($root . '/', '', $path);

        if (isExcluded($relative, $excludedPrefixes)) {
            continue;
        }

        $files[$relative] = $path;
    }

    ksort($files);
    return $files;
}

/**
 * @param list<string> $excludedPrefixes
 */
function isExcluded(string $relative, array $excludedPrefixes): bool
{
    foreach ($excludedPrefixes as $prefix) {
        if (str_starts_with($relative, $prefix)) {
            return true;
        }
    }

    return false;
}
