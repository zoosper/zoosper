<?php

declare(strict_types=1);

/**
 * Generate a local candidate patch/report for the RoleAdminController Latte cutover.
 *
 * This command is non-mutating. It writes generated artefacts under var/reports only.
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
$templatePaths = [
    'app/zoosper-core/views/admin/roles/index.latte',
    'app/zoosper-core/views/admin/roles/form.latte',
];
$errors = [];

if ($controllerPath === null) {
    $errors[] = 'RoleAdminController.php was not found under app/.';
}

foreach ($templatePaths as $templatePath) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $templatePath))) {
        $errors[] = 'Required template missing: ' . $templatePath;
    }
}

$source = $controllerPath !== null ? (string) file_get_contents($controllerPath) : '';
$publicMethods = publicMethods($source);
$constructorParams = constructorParams($source);
$renderSignals = discoverRenderSignals($root . DIRECTORY_SEPARATOR . 'app');
$inlineSignals = [
    'contains_form_markup' => str_contains($source, '<form'),
    'contains_table_markup' => str_contains($source, '<table'),
    'contains_heredoc' => str_contains($source, '<<<'),
    'mentions_csrf' => stripos($source, 'csrf') !== false,
    'mentions_role' => stripos($source, 'role') !== false,
];

$patchPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-cutover-candidate.patch';
$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-cutover-generation.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-cutover-generation.log';

$patch = candidatePatchBrief($controllerPath, $publicMethods, $constructorParams, $renderSignals, $inlineSignals, $errors);
file_put_contents($patchPath, $patch . PHP_EOL);

$report = [];
$report[] = '# RoleAdminController Cutover Patch Generation';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Repo root: ' . $root;
$report[] = 'Controller path: ' . ($controllerPath ?? 'not found');
$report[] = 'Candidate patch: ' . $patchPath;
$report[] = 'Errors: ' . count($errors);
$report[] = '';
$report[] = '## Public methods';
$report[] = '';
foreach ($publicMethods as $method) {
    $report[] = '- ' . $method;
}
if ($publicMethods === []) {
    $report[] = '- none detected';
}
$report[] = '';
$report[] = '## Constructor parameters';
$report[] = '';
foreach ($constructorParams as $param) {
    $report[] = '- ' . $param;
}
if ($constructorParams === []) {
    $report[] = '- none detected';
}
$report[] = '';
$report[] = '## Inline/source signals';
$report[] = '';
foreach ($inlineSignals as $name => $value) {
    $report[] = '- ' . $name . ': ' . ($value ? 'yes' : 'no');
}
$report[] = '';
$report[] = '## Render/view signals';
$report[] = '';
foreach ($renderSignals as $signal) {
    $report[] = '- ' . $signal;
}
if ($renderSignals === []) {
    $report[] = '- none detected';
}

if ($errors !== []) {
    $report[] = '';
    $report[] = '## Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($reportPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Role admin cutover candidate patch written to: ' . $patchPath;
$log[] = 'Role admin cutover generation report written to: ' . $reportPath;
$log[] = 'CONTROLLER_FOUND ' . ($controllerPath !== null ? 'yes' : 'no');
$log[] = 'TEMPLATE_COUNT ' . count($templatePaths);
$log[] = 'PUBLIC_METHODS ' . count($publicMethods);
$log[] = 'RENDER_SIGNALS ' . count($renderSignals);
$log[] = 'GENERATION_ERRORS ' . count($errors);
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

/** @return list<string> */
function publicMethods(string $source): array
{
    if (! preg_match_all('/public\s+function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $source, $matches)) {
        return [];
    }

    return array_values(array_unique($matches[1]));
}

/** @return list<string> */
function constructorParams(string $source): array
{
    if (! preg_match('/function\s+__construct\s*\((.*?)\)/s', $source, $match)) {
        return [];
    }

    return array_values(array_filter(array_map('trim', explode(',', $match[1]))));
}

/** @return list<string> */
function discoverRenderSignals(string $directory): array
{
    if (! is_dir($directory)) {
        return [];
    }

    $signals = [];
    $needles = ['TemplateRenderer', 'TemplateView', 'Latte', 'render(', 'ViewRenderer'];
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

        if (count($signals) >= 50) {
            break;
        }
    }

    return array_values(array_unique($signals));
}

/**
 * @param list<string> $publicMethods
 * @param list<string> $constructorParams
 * @param list<string> $renderSignals
 * @param array<string,bool> $inlineSignals
 * @param list<string> $errors
 */
function candidatePatchBrief(?string $controllerPath, array $publicMethods, array $constructorParams, array $renderSignals, array $inlineSignals, array $errors): string
{
    $lines = [];
    $lines[] = '# Candidate patch brief for RoleAdminController Latte cutover';
    $lines[] = '# Generated report only; this is not an automatically applicable diff unless a future phase makes it source-specific.';
    $lines[] = '';
    $lines[] = 'Controller: ' . ($controllerPath ?? 'not found');
    $lines[] = 'Errors: ' . count($errors);
    $lines[] = '';
    $lines[] = 'Detected public methods: ' . ($publicMethods === [] ? 'none' : implode(', ', $publicMethods));
    $lines[] = 'Detected constructor parameters: ' . ($constructorParams === [] ? 'none' : implode(' | ', $constructorParams));
    $lines[] = 'Render/view signals: ' . ($renderSignals === [] ? 'none' : (string) count($renderSignals));
    $lines[] = '';
    $lines[] = 'Inline markup signals:';
    foreach ($inlineSignals as $name => $value) {
        $lines[] = '- ' . $name . ': ' . ($value ? 'yes' : 'no');
    }
    $lines[] = '';
    $lines[] = 'Recommended source-specific patch:';
    $lines[] = '- Use the existing render/view convention detected in the source tree.';
    $lines[] = '- Render role list output through app/zoosper-core/views/admin/roles/index.latte.';
    $lines[] = '- Render role form output through app/zoosper-core/views/admin/roles/form.latte.';
    $lines[] = '- Preserve current route paths, ACL names, redirects, CSRF token names, and repository/service calls.';
    $lines[] = '- Add/update a regression test that RoleAdminController.php no longer owns large <form, <table, or heredoc HTML blocks.';
    $lines[] = '';
    $lines[] = 'No source changes were made by this generator.';

    return implode(PHP_EOL, $lines);
}
