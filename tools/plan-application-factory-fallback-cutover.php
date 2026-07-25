<?php

declare(strict_types=1);

/**
 * ApplicationFactory fallback cutover planner.
 *
 * Read-only planner that inspects the live ApplicationFactory shape and writes
 * a patch draft for replacing direct PageController coupling with the
 * core-owned fallback handler boundary.
 */

$root = dirname(__DIR__);
$reportDir = $root . '/var/reports';
$target = $root . '/app/zoosper-core/src/Bootstrap/ApplicationFactory.php';

$errors = [];
$warnings = [];
$observations = [];
$findings = [];

if (!is_file($target)) {
    $errors[] = 'Missing ApplicationFactory target: app/zoosper-core/src/Bootstrap/ApplicationFactory.php';
} else {
    $source = (string) file_get_contents($target);
    $lines = preg_split('/\R/', $source) ?: [];

    $hasDirectPageImport = str_contains($source, 'use Zoosper\\Page\\Controller\\PageController;');
    $hasPageControllerToken = str_contains($source, 'PageController');
    $hasFallbackInterfaceImport = str_contains($source, 'use Zoosper\\Core\\Routing\\FallbackHandlerInterface;');
    $hasNullFallbackImport = str_contains($source, 'use Zoosper\\Core\\Routing\\NullFallbackHandler;');
    $hasFallbackInterfaceToken = str_contains($source, 'FallbackHandlerInterface');
    $hasNullFallbackToken = str_contains($source, 'NullFallbackHandler');

    $findings['hasDirectPageImport'] = $hasDirectPageImport;
    $findings['hasPageControllerToken'] = $hasPageControllerToken;
    $findings['hasFallbackInterfaceImport'] = $hasFallbackInterfaceImport;
    $findings['hasNullFallbackImport'] = $hasNullFallbackImport;
    $findings['hasFallbackInterfaceToken'] = $hasFallbackInterfaceToken;
    $findings['hasNullFallbackToken'] = $hasNullFallbackToken;

    if ($hasDirectPageImport) {
        $warnings[] = 'ApplicationFactory still imports PageController directly.';
    }
    if (!$hasFallbackInterfaceToken) {
        $warnings[] = 'ApplicationFactory does not yet reference FallbackHandlerInterface.';
    }
    if (!$hasNullFallbackToken) {
        $warnings[] = 'ApplicationFactory does not yet reference NullFallbackHandler.';
    }

    $pageControllerLines = [];
    foreach ($lines as $index => $line) {
        if (str_contains($line, 'PageController')) {
            $pageControllerLines[] = [
                'line' => $index + 1,
                'source' => trim($line),
            ];
        }
    }
    $findings['pageControllerLines'] = $pageControllerLines;

    $useBlockLastLine = null;
    foreach ($lines as $index => $line) {
        if (str_starts_with(trim($line), 'use ')) {
            $useBlockLastLine = $index + 1;
        }
    }
    $findings['useBlockLastLine'] = $useBlockLastLine;

    $draft = buildPatchDraft($findings, $pageControllerLines);

    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0775, true);
    }
    file_put_contents($reportDir . '/application-factory-fallback-cutover-draft.patch.md', $draft);

    if ($pageControllerLines === []) {
        $observations[] = 'No PageController token remains in ApplicationFactory.';
    } else {
        $observations[] = 'PageController token occurrences found: ' . count($pageControllerLines);
    }
}

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$payload = [
    'generatedAt' => gmdate('c'),
    'errors' => $errors,
    'warnings' => $warnings,
    'observations' => $observations,
    'findings' => $findings,
];

$report = [];
$report[] = '## ApplicationFactory Fallback Cutover Plan';
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
$report[] = '### PageController occurrences';
foreach (($findings['pageControllerLines'] ?? []) as $item) {
    $report[] = '- line ' . $item['line'] . ': ' . $item['source'];
}
$report[] = '';
$report[] = '### Patch draft';
$report[] = '- See var/reports/application-factory-fallback-cutover-draft.patch.md';

if ($errors !== []) {
    $report[] = '';
    $report[] = '### Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($reportDir . '/application-factory-fallback-cutover-plan.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/application-factory-fallback-cutover-plan.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);

/**
 * @param list<array{line:int,source:string}> $pageControllerLines
 */
function buildPatchDraft(array $findings, array $pageControllerLines): string
{
    $lines = [];
    $lines[] = '# ApplicationFactory fallback cutover patch draft';
    $lines[] = '';
    $lines[] = 'This is a human-reviewed draft. It is not automatically applied.';
    $lines[] = '';
    $lines[] = '## Intended import changes';
    $lines[] = '';
    $lines[] = 'Remove:';
    $lines[] = '';
    $lines[] = '```php';
    $lines[] = 'use Zoosper\\Page\\Controller\\PageController;';
    $lines[] = '```';
    $lines[] = '';
    $lines[] = 'Add if not already present:';
    $lines[] = '';
    $lines[] = '```php';
    $lines[] = 'use Zoosper\\Core\\Routing\\FallbackHandlerInterface;';
    $lines[] = 'use Zoosper\\Core\\Routing\\NullFallbackHandler;';
    $lines[] = '```';
    $lines[] = '';
    $lines[] = '## PageController occurrences to replace';
    foreach ($pageControllerLines as $item) {
        $lines[] = '- line ' . $item['line'] . ': `' . $item['source'] . '`';
    }
    $lines[] = '';
    $lines[] = '## Intended runtime shape';
    $lines[] = '';
    $lines[] = '```php';
    $lines[] = '$fallbackHandler = $container->has(FallbackHandlerInterface::class)';
    $lines[] = '    ? $container->get(FallbackHandlerInterface::class)';
    $lines[] = '    : new NullFallbackHandler();';
    $lines[] = '```';
    $lines[] = '';
    $lines[] = 'The exact insertion point must be selected in Phase 1.69m-z based on the live ApplicationFactory constructor/container shape.';

    return implode("\n", $lines) . "\n";
}
