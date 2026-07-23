<?php

declare(strict_types=1);

/**
 * Export compact source context for the RoleAdminController Latte cutover.
 *
 * This command is read-only with respect to source. It writes artefacts under var/reports only.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

$contextDir = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-cutover-context';
$manifestPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-cutover-context-manifest.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-cutover-context.log';
$zipPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-cutover-context.zip';

if (! is_dir($contextDir) && ! mkdir($contextDir, 0775, true) && ! is_dir($contextDir)) {
    fwrite(STDERR, 'Unable to create context directory: ' . $contextDir . PHP_EOL);
    exit(1);
}

$controllerPath = findFile($root . DIRECTORY_SEPARATOR . 'app', 'RoleAdminController.php');
$errors = [];
$copied = [];

if ($controllerPath === null) {
    $errors[] = 'RoleAdminController.php was not found under app/.';
}

$knownFiles = [
    'app/zoosper-core/views/admin/roles/index.latte',
    'app/zoosper-core/views/admin/roles/form.latte',
    'docs/development/role-admin-latte-closeout.md',
    'docs/development/role-admin-latte-closeout-handoff.md',
    'docs/development/role-admin-render-integration.md',
    'docs/development/role-admin-template-contract.md',
    'docs/development/role-admin-template-scaffold.md',
];

if ($controllerPath !== null) {
    $copied[] = copyIntoContext($root, $contextDir, $controllerPath);
}

foreach ($knownFiles as $relativePath) {
    $absolute = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    if (is_file($absolute)) {
        $copied[] = copyIntoContext($root, $contextDir, $absolute);
    }
}

foreach (discoverRelevantConfigFiles($root) as $absolute) {
    $copied[] = copyIntoContext($root, $contextDir, $absolute);
}

foreach (discoverRenderSignalFiles($root . DIRECTORY_SEPARATOR . 'app') as $absolute) {
    $copied[] = copyIntoContext($root, $contextDir, $absolute);
}

$source = $controllerPath !== null ? (string) file_get_contents($controllerPath) : '';
$manifest = [];
$manifest[] = '# RoleAdminController Cutover Context Manifest';
$manifest[] = '';
$manifest[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$manifest[] = 'Repo root: ' . $root;
$manifest[] = 'Context directory: ' . $contextDir;
$manifest[] = 'Controller path: ' . ($controllerPath ?? 'not found');
$manifest[] = 'Errors: ' . count($errors);
$manifest[] = 'Files copied: ' . count(array_filter($copied));
$manifest[] = '';
$manifest[] = '## Public methods';
foreach (publicMethods($source) as $method) {
    $manifest[] = '- ' . $method;
}
$manifest[] = '';
$manifest[] = '## Constructor parameters';
foreach (constructorParams($source) as $param) {
    $manifest[] = '- ' . $param;
}
$manifest[] = '';
$manifest[] = '## Inline signals';
$manifest[] = '- contains_form_markup: ' . (str_contains($source, '<form') ? 'yes' : 'no');
$manifest[] = '- contains_table_markup: ' . (str_contains($source, '<table') ? 'yes' : 'no');
$manifest[] = '- contains_input_markup: ' . (str_contains($source, '<input') ? 'yes' : 'no');
$manifest[] = '- contains_heredoc: ' . (str_contains($source, '<<<') ? 'yes' : 'no');
$manifest[] = '- mentions_csrf: ' . (stripos($source, 'csrf') !== false ? 'yes' : 'no');
$manifest[] = '- mentions_role: ' . (stripos($source, 'role') !== false ? 'yes' : 'no');
$manifest[] = '';
$manifest[] = '## Copied files';
foreach (array_filter($copied) as $relative) {
    $manifest[] = '- ' . $relative;
}

if ($errors !== []) {
    $manifest[] = '';
    $manifest[] = '## Errors';
    foreach ($errors as $error) {
        $manifest[] = '- ' . $error;
    }
}

file_put_contents($manifestPath, implode(PHP_EOL, $manifest) . PHP_EOL);
copy($manifestPath, $contextDir . DIRECTORY_SEPARATOR . 'manifest.txt');

$zipCreated = false;
if (class_exists(ZipArchive::class)) {
    if (is_file($zipPath)) {
        unlink($zipPath);
    }

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($contextDir, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $zip->addFile($file->getPathname(), str_replace($contextDir . DIRECTORY_SEPARATOR, '', $file->getPathname()));
            }
        }
        $zip->close();
        $zipCreated = is_file($zipPath);
    }
}

$log = [];
$log[] = 'Role admin cutover context directory: ' . $contextDir;
$log[] = 'Role admin cutover context manifest: ' . $manifestPath;
$log[] = 'CONTEXT_FILES ' . count(array_filter($copied));
$log[] = 'ZIP_CREATED ' . ($zipCreated ? 'yes' : 'no');
$log[] = 'CONTEXT_ERRORS ' . count($errors);
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

function copyIntoContext(string $root, string $contextDir, string $absolutePath): ?string
{
    $relative = str_replace($root . DIRECTORY_SEPARATOR, '', $absolutePath);
    $target = $contextDir . DIRECTORY_SEPARATOR . str_replace(DIRECTORY_SEPARATOR, '__', $relative);
    if (! is_file($absolutePath)) {
        return null;
    }
    copy($absolutePath, $target);
    return $relative;
}

/** @return list<string> */
function discoverRelevantConfigFiles(string $root): array
{
    $results = [];
    foreach (['app', 'config'] as $base) {
        $dir = $root . DIRECTORY_SEPARATOR . $base;
        if (! is_dir($dir)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (! $file->isFile() || ! in_array($file->getExtension(), ['php', 'md'], true)) {
                continue;
            }
            $path = $file->getPathname();
            $contents = (string) file_get_contents($path);
            if (stripos($contents, 'RoleAdminController') !== false || stripos($contents, 'admin/roles') !== false || stripos($contents, 'role.manage') !== false) {
                $results[] = $path;
            }
            if (count($results) >= 30) {
                break 2;
            }
        }
    }
    return array_values(array_unique($results));
}

/** @return list<string> */
function discoverRenderSignalFiles(string $directory): array
{
    if (! is_dir($directory)) {
        return [];
    }
    $results = [];
    $needles = ['TemplateRenderer', 'TemplateView', 'Latte', 'ViewRenderer', 'render('];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $contents = (string) file_get_contents($file->getPathname());
        foreach ($needles as $needle) {
            if (str_contains($contents, $needle)) {
                $results[] = $file->getPathname();
                break;
            }
        }
        if (count($results) >= 30) {
            break;
        }
    }
    return array_values(array_unique($results));
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
