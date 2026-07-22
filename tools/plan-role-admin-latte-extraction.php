<?php

declare(strict_types=1);

/**
 * Read-only planner for extracting RoleAdminController markup into Latte templates.
 *
 * This command does not rewrite source files.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, 'Unable to create output directory: ' . $outputDir . PHP_EOL);
    exit(1);
}

$controllerPath = findFile($root . DIRECTORY_SEPARATOR . 'app', 'RoleAdminController.php');
$contractPath = $root . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'development' . DIRECTORY_SEPARATOR . 'role-admin-template-contract.md';
$errors = [];

if ($controllerPath === null) {
    $errors[] = 'RoleAdminController.php was not found under app/';
}

if (! is_file($contractPath)) {
    $errors[] = 'Role admin template contract document was not found.';
}

$source = $controllerPath !== null ? (string) file_get_contents($controllerPath) : '';

$methodNames = discoverPublicMethods($source);
$signals = [
    'controller_found' => $controllerPath !== null,
    'contract_doc_exists' => is_file($contractPath),
    'public_method_count' => (string) count($methodNames),
    'contains_form_markup' => str_contains($source, '<form') ? 'yes' : 'no',
    'contains_table_markup' => str_contains($source, '<table') ? 'yes' : 'no',
    'contains_heredoc' => str_contains($source, '<<<') ? 'yes' : 'no',
    'mentions_csrf' => stripos($source, 'csrf') !== false ? 'yes' : 'no',
    'mentions_role' => stripos($source, 'role') !== false ? 'yes' : 'no',
];

$templateTargets = [
    'app/zoosper-core/views/admin/roles/index.latte',
    'app/zoosper-core/views/admin/roles/form.latte',
];

$txtPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-latte-extraction-plan.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-latte-extraction-plan.log';

$report = [];
$report[] = '# RoleAdminController Latte Extraction Plan';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Repo root: ' . $root;
$report[] = 'Controller path: ' . ($controllerPath ?? 'not found');
$report[] = 'Errors: ' . count($errors);
$report[] = '';
$report[] = '## Public methods found';
$report[] = '';

foreach ($methodNames as $methodName) {
    $report[] = '- ' . $methodName;
}

if ($methodNames === []) {
    $report[] = '- none detected by planner';
}

$report[] = '';
$report[] = '## Source signals';
$report[] = '';
foreach ($signals as $name => $value) {
    $report[] = '- ' . $name . ': ' . $value;
}

$report[] = '';
$report[] = '## Suggested template targets';
$report[] = '';
foreach ($templateTargets as $target) {
    $report[] = '- ' . $target;
}

$report[] = '';
$report[] = '## Next implementation checklist';
$report[] = '';
$report[] = '- Create role list and role form Latte templates.';
$report[] = '- Move controller-owned markup into templates.';
$report[] = '- Preserve existing route paths, permission semantics, CSRF behaviour, and redirects.';
$report[] = '- Add a regression test proving large inline HTML/heredoc no longer lives in RoleAdminController.';

if ($errors !== []) {
    $report[] = '';
    $report[] = '## Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($txtPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Role admin latte extraction plan written to: ' . $txtPath;
$log[] = 'CONTROLLER_FOUND ' . ($controllerPath !== null ? 'yes' : 'no');
$log[] = 'PUBLIC_METHODS ' . count($methodNames);
$log[] = 'EXTRACTION_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;

if ($errors !== []) {
    exit(1);
}

function findFile(string $directory, string $filename): ?string
{
    if (! is_dir($directory)) {
        return null;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() === $filename) {
            return $file->getPathname();
        }
    }

    return null;
}

/**
 * @return list<string>
 */
function discoverPublicMethods(string $source): array
{
    if ($source === '') {
        return [];
    }

    if (! preg_match_all('/public\s+function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $source, $matches)) {
        return [];
    }

    return array_values(array_unique($matches[1]));
}
