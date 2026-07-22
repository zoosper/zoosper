<?php

declare(strict_types=1);

/**
 * Read-only audit for the RoleAdminController Latte template scaffold.
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

$templatePaths = [
    'app/zoosper-core/views/admin/roles/index.latte',
    'app/zoosper-core/views/admin/roles/form.latte',
];

$controllerPath = findFile($root . DIRECTORY_SEPARATOR . 'app', 'RoleAdminController.php');
$errors = [];
$checks = [
    'controller_found' => $controllerPath !== null,
    'scaffold_doc_exists' => is_file($root . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'development' . DIRECTORY_SEPARATOR . 'role-admin-template-scaffold.md'),
];

foreach ($templatePaths as $templatePath) {
    $absolute = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $templatePath);
    $checks[$templatePath . '_exists'] = is_file($absolute);
    $checks[$templatePath . '_mentions_csrf'] = is_file($absolute) && stripos((string) file_get_contents($absolute), 'csrf') !== false;
}

foreach ($checks as $name => $passed) {
    if (! $passed) {
        $errors[] = $name . ' failed';
    }
}

$txtPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-template-scaffold.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-template-scaffold.log';

$report = [];
$report[] = '# Role Admin Template Scaffold Audit';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Repo root: ' . $root;
$report[] = 'Controller path: ' . ($controllerPath ?? 'not found');
$report[] = 'Errors: ' . count($errors);
$report[] = '';

foreach ($checks as $name => $passed) {
    $report[] = '- ' . $name . ': ' . ($passed ? 'yes' : 'no');
}

if ($errors !== []) {
    $report[] = '';
    $report[] = '## Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($txtPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Role admin template scaffold audit written to: ' . $txtPath;
$log[] = 'TEMPLATE_COUNT ' . count($templatePaths);
$log[] = 'SCAFFOLD_ERRORS ' . count($errors);
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
