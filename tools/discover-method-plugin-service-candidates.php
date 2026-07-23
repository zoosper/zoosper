<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$scanRoots = [
    $root . '/app/zoosper-core/src',
    $root . '/app/zoosper-admin/src',
    $root . '/app/zoosper-auth/src',
    $root . '/app/zoosper-page/src',
];

$excludeFragments = [
    '/Plugin/',
    '/Http/Middleware/',
    '/Controller/',
    '/tests/',
];

$methodPattern = '/public\s+function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/';
$classPattern = '/namespace\s+([^;]+);.*?(?:final\s+|abstract\s+)?class\s+([A-Za-z_][A-Za-z0-9_]*)/s';
$candidates = [];
$errors = 0;
$report = [];

$report[] = '## Method Plugin Internal Service Candidate Discovery';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

foreach ($scanRoots as $scanRoot) {
    if (!is_dir($scanRoot)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        $path = $file->getPathname();
        if (!str_ends_with($path, '.php')) {
            continue;
        }

        $relative = str_replace($root . '/', '', $path);
        foreach ($excludeFragments as $fragment) {
            if (str_contains($relative, trim($fragment, '/'))) {
                continue 2;
            }
        }

        $source = (string) file_get_contents($path);
        if (!preg_match($classPattern, $source, $classMatch)) {
            continue;
        }

        $fqcn = $classMatch[1] . '\\' . $classMatch[2];
        if (!preg_match_all($methodPattern, $source, $methodMatches)) {
            continue;
        }

        foreach ($methodMatches[1] as $method) {
            if (str_starts_with($method, '__')) {
                continue;
            }

            $key = $fqcn . '::' . $method;
            $score = 0;
            if (str_contains($relative, '/Service/')) {
                $score += 3;
            }
            if (str_contains($relative, '/Repository/')) {
                $score += 1;
            }
            if (in_array($method, ['render', 'load', 'save', 'resolve', 'build', 'generate', 'process'], true)) {
                $score += 2;
            }

            $candidates[$key] = [
                'key' => $key,
                'class' => $fqcn,
                'method' => $method,
                'file' => $relative,
                'score' => $score,
            ];
        }
    }
}

uasort($candidates, static fn (array $a, array $b): int => [$b['score'], $a['key']] <=> [$a['score'], $b['key']]);
$candidates = array_values($candidates);

$report[] = 'Candidates found: ' . count($candidates);
$report[] = '';
$report[] = '### Top candidates';
foreach (array_slice($candidates, 0, 40) as $candidate) {
    $report[] = '- ' . $candidate['key'] . ' | score=' . $candidate['score'] . ' | file=' . $candidate['file'];
}

$report[] = '';
$report[] = 'Runtime plugin execution enabled: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-service-candidates.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-service-candidates.json', json_encode($candidates, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
file_put_contents($reportDir . '/method-plugin-service-candidates.log', "METHOD_PLUGIN_SERVICE_CANDIDATES_ERRORS {$errors}\nMETHOD_PLUGIN_SERVICE_CANDIDATES_COUNT " . count($candidates) . "\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
