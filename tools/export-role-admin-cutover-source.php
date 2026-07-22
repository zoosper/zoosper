<?php

declare(strict_types=1);

/**
 * Export exact RoleAdminController source context for the Latte cutover implementation.
 *
 * This command is read-only. It writes reports under var/reports only.
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
$errors = [];

if ($controllerPath === null) {
    $errors[] = 'RoleAdminController.php was not found under app/';
}

$source = $controllerPath !== null ? (string) file_get_contents($controllerPath) : '';
$publicMethods = publicMethods($source);
$constructorParams = constructorParams($source);
$renderSignals = discoverRenderSignals($root . DIRECTORY_SEPARATOR . 'app');
$templatePaths = [
    'app/zoosper-core/views/admin/roles/index.latte',
    'app/zoosper-core/views/admin/roles/form.latte',
];

foreach ($templatePaths as $templatePath) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $templatePath))) {
        $errors[] = 'Template missing: ' . $templatePath;
    }
}

$inlineSignals = [
    'contains_form_markup' => str_contains($source, '<form') ? 'yes' : 'no',
    'contains_table_markup' => str_contains($source, '<table') ? 'yes' : 'no',
    'contains_heredoc' => str_contains($source, '<<<') ? 'yes' : 'no',
    'mentions_csrf' => stripos($source, 'csrf') !== false ? 'yes' : 'no',
    'mentions_role' => stripos($source, 'role') !== false ? 'yes' : 'no',
];

$txtPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-cutover-source.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-cutover-source.log';

$report = [];
$report[] = '# RoleAdminController Cutover Source Capture';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Repo root: ' . $root;
$report[] = 'Controller path: ' . ($controllerPath ?? 'not found');
$report[] = 'Errors: ' . count($errors);
$report[] = '';

$report[] = '## Template paths';
$report[] = '';
foreach ($templatePaths as $templatePath) {
    $exists = is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $templatePath));
    $report[] = '- ' . $templatePath . ': ' . ($exists ? 'exists' : 'missing');
}

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
    $report[] = '- ' . $name . ': ' . $value;
}

$report[] = '';
$report[] = '## Render/view source signals';
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

$report[] = '';
$report[] = '## Full RoleAdminController source with line numbers';
$report[] = '';
$report[] = '```php';
foreach (preg_split('/\R/', $source) ?: [] as $index => $line) {
    $report[] = str_pad((string) ($index + 1), 4, ' ', STR_PAD_LEFT) . ': ' . $line;
}
$report[] = '```';

file_put_contents($txtPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Role admin cutover source written to: ' . $txtPath;
$log[] = 'CONTROLLER_FOUND ' . ($controllerPath !== null ? 'yes' : 'no');
$log[] = 'PUBLIC_METHODS ' . count($publicMethods);
$log[] = 'CONSTRUCTOR_PARAMS ' . count($constructorParams);
$log[] = 'RENDER_SIGNALS ' . count($renderSignals);
$log[] = 'SOURCE_CAPTURE_ERRORS ' . count($errors);
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

    $params = array_filter(array_map('trim', explode(',', $match[1])));
    return array_values($params);
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

        if (count($signals) >= 40) {
            break;
        }
    }

    return array_values(array_unique($signals));
}
