<?php

declare(strict_types=1);

/**
 * Read-only audit for RoleAdminController render integration readiness.
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
$indexTemplate = 'app/zoosper-core/views/admin/roles/index.latte';
$formTemplate = 'app/zoosper-core/views/admin/roles/form.latte';
$docPath = 'docs/development/role-admin-render-integration.md';

$source = $controllerPath !== null ? (string) file_get_contents($controllerPath) : '';
$errors = [];

$checks = [
    'controller_found' => $controllerPath !== null,
    'index_template_exists' => is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $indexTemplate)),
    'form_template_exists' => is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $formTemplate)),
    'render_integration_doc_exists' => is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $docPath)),
    'controller_mentions_role' => stripos($source, 'role') !== false,
    'controller_mentions_csrf' => stripos($source, 'csrf') !== false,
    'controller_possible_inline_html' => str_contains($source, '<form') || str_contains($source, '<table') || str_contains($source, '<<<'),
];

foreach (['controller_found', 'index_template_exists', 'form_template_exists', 'render_integration_doc_exists'] as $required) {
    if (! $checks[$required]) {
        $errors[] = $required . ' failed';
    }
}

$renderSignals = discoverRenderSourceSignals($root . DIRECTORY_SEPARATOR . 'app');

$txtPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-render-integration.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-render-integration.log';

$report = [];
$report[] = '# Role Admin Render Integration Readiness';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Repo root: ' . $root;
$report[] = 'Controller path: ' . ($controllerPath ?? 'not found');
$report[] = 'Errors: ' . count($errors);
$report[] = '';
$report[] = '## Checks';
$report[] = '';
foreach ($checks as $name => $passed) {
    $report[] = '- ' . $name . ': ' . ($passed ? 'yes' : 'no');
}

$report[] = '';
$report[] = '## Render-related source signals';
$report[] = '';
if ($renderSignals === []) {
    $report[] = '- none detected by audit';
} else {
    foreach ($renderSignals as $signal) {
        $report[] = '- ' . $signal;
    }
}

$report[] = '';
$report[] = '## Notes';
$report[] = '';
$report[] = '- Inline HTML remains a readiness signal only; actual removal belongs to the implementation phase.';
$report[] = '- This audit deliberately does not rewrite source files.';

if ($errors !== []) {
    $report[] = '';
    $report[] = '## Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($txtPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Role admin render integration readiness written to: ' . $txtPath;
$log[] = 'CONTROLLER_FOUND ' . ($controllerPath !== null ? 'yes' : 'no');
$log[] = 'TEMPLATES_READY ' . ($checks['index_template_exists'] && $checks['form_template_exists'] ? 'yes' : 'no');
$log[] = 'RENDER_SIGNALS ' . count($renderSignals);
$log[] = 'INTEGRATION_ERRORS ' . count($errors);
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
function discoverRenderSourceSignals(string $directory): array
{
    if (! is_dir($directory)) {
        return [];
    }

    $signals = [];
    $needles = ['TemplateRenderer', 'TemplateView', 'Latte', 'render('];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $contents = (string) file_get_contents($file->getPathname());
        foreach ($needles as $needle) {
            if (str_contains($contents, $needle)) {
                $signals[] = str_replace($directory . DIRECTORY_SEPARATOR, '', $file->getPathname()) . ' contains ' . $needle;
                break;
            }
        }

        if (count($signals) >= 20) {
            break;
        }
    }

    return array_values(array_unique($signals));
}
