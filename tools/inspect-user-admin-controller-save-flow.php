<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$repositoryPath = find_file_containing($basePath, 'class AdminUserRepository');
$reportPath = $basePath . '/var/reports/user-admin-save-flow-inspection.txt';

print "Zoosper UserAdminController save-flow inspection\n";
print "================================================\n\n";

if (!is_file($controllerPath)) {
    fwrite(STDERR, "Missing UserAdminController: {$controllerPath}\n");
    exit(2);
}

$controller = (string) file_get_contents($controllerPath);
$repository = $repositoryPath !== null ? (string) file_get_contents($repositoryPath) : '';
$controllerLines = preg_split('/\R/', $controller) ?: [];
$repositoryLines = $repository !== '' ? (preg_split('/\R/', $repository) ?: []) : [];

$signals = [
    'controller_has_locale_form_field' => contains_any($controller, ['name="locale"', "name='locale'"]),
    'controller_references_admin_user_save_pipeline' => str_contains($controller, 'AdminUserSavePipeline'),
    'controller_has_post_superglobal' => str_contains($controller, '$_POST'),
    'controller_has_request_post' => contains_any($controller, ['->post(', 'getParsedBody', 'request->request']),
    'controller_constructs_admin_user' => str_contains($controller, 'new AdminUser('),
    'controller_repository_save_update_literal' => preg_match('/->(?:save|update)\s*\(/', $controller) === 1,
    'repository_found' => $repositoryPath !== null,
    'repository_has_update_sql' => preg_match('/UPDATE\s+`?admin_users`?/i', $repository) === 1,
    'repository_has_insert_sql' => preg_match('/INSERT\s+INTO\s+`?admin_users`?/i', $repository) === 1,
    'repository_has_pdo_prepare' => str_contains($repository, '->prepare('),
];

$controllerMatches = matching_lines($controllerLines, [
    'function ', '$_POST', 'getParsedBody', 'request->request', 'new AdminUser(', 'AdminUserRepository', '->save(', '->update(', '->create(', '->insert(', '->persist(', 'name="locale"', "name='locale'", 'notice-success', 'Admin user saved',
]);
$repositoryMatches = matching_lines($repositoryLines, [
    'function ', 'INSERT INTO', 'UPDATE ', 'admin_users', '->prepare(', 'execute(', 'locale',
]);

$report = [];
$report[] = 'Zoosper UserAdminController save-flow inspection report';
$report[] = '=====================================================';
$report[] = 'Controller: app/zoosper-admin/src/Controller/UserAdminController.php';
$report[] = 'Repository: ' . ($repositoryPath !== null ? relative_path($basePath, $repositoryPath) : 'not found');
$report[] = '';
$report[] = 'Detected signals:';
foreach ($signals as $name => $value) {
    $report[] = '- ' . $name . ': ' . ($value ? 'yes' : 'no');
}
$report[] = '';
$report[] = 'Controller relevant lines:';
foreach ($controllerMatches as [$line, $text]) {
    $report[] = str_pad((string) $line, 5, ' ', STR_PAD_LEFT) . ': ' . $text;
}
$report[] = '';
$report[] = 'Repository relevant lines:';
foreach ($repositoryMatches as [$line, $text]) {
    $report[] = str_pad((string) $line, 5, ' ', STR_PAD_LEFT) . ': ' . $text;
}
$report[] = '';
$report[] = 'Recommended next migration rule:';
$report[] = '- Do not patch UserAdminController until the actual save method and persistence call shape are identified.';
$report[] = '- Migrate by replacing the real save data collection with AdminUserSavePipeline::data/context/updateSql or a repository method using its generated core write data.';
$report[] = '- Keep password and role_ids on dedicated handlers; do not write them through core column SQL.';

if (!is_dir(dirname($reportPath))) {
    mkdir(dirname($reportPath), 0775, true);
}
file_put_contents($reportPath, implode(PHP_EOL, $report) . PHP_EOL);

foreach ($report as $line) {
    print $line . PHP_EOL;
}
print "\nReport: {$reportPath}\n";
print "Result: OK\n";

function contains_any(string $haystack, array $needles): bool
{
    foreach ($needles as $needle) {
        if (str_contains($haystack, $needle)) {
            return true;
        }
    }

    return false;
}

/** @return list<array{int,string}> */
function matching_lines(array $lines, array $needles): array
{
    $matches = [];
    foreach ($lines as $index => $line) {
        foreach ($needles as $needle) {
            if (str_contains($line, $needle)) {
                $matches[] = [$index + 1, trim($line)];
                break;
            }
        }
    }

    return $matches;
}

function find_file_containing(string $basePath, string $needle): ?string
{
    foreach ([$basePath . '/app', $basePath . '/modules'] as $root) {
        if (!is_dir($root)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->getExtension() === 'php') {
                $path = $file->getPathname();
                if (str_contains((string) file_get_contents($path), $needle)) {
                    return $path;
                }
            }
        }
    }

    return null;
}

function relative_path(string $basePath, string $path): string
{
    return str_starts_with($path, $basePath . '/') ? substr($path, strlen($basePath) + 1) : $path;
}
