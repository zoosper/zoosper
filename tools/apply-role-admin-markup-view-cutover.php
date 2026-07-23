<?php

declare(strict_types=1);

/**
 * Guarded source-specific RoleAdminController markup view cutover.
 *
 * Default mode is read-only. Use --apply to rewrite the confirmed markup-owning methods.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';
$apply = false;

foreach ($argv as $argument) {
    if ($argument === '--apply') {
        $apply = true;
    }
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, 'Unable to create output directory: ' . $outputDir . PHP_EOL);
    exit(1);
}

$controllerRelative = 'app/zoosper-admin/src/Controller/RoleAdminController.php';
$controllerPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $controllerRelative);
$requiredViews = [
    'app/zoosper-admin/resources/views/admin/roles/index.php',
    'app/zoosper-admin/resources/views/admin/roles/form.php',
    'app/zoosper-admin/resources/views/admin/roles/permission-tree.php',
    'app/zoosper-admin/resources/views/admin/roles/user-assignment.php',
];
$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-markup-view-cutover.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'role-admin-markup-view-cutover.log';

$errors = [];
$actions = [];

if (! is_file($controllerPath)) {
    $errors[] = 'Controller not found: ' . $controllerRelative;
}

foreach ($requiredViews as $view) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $view))) {
        $errors[] = 'Required view missing: ' . $view;
    }
}

$source = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
$beforeSignals = markupSignals($source);
$canApply = $errors === []
    && str_contains($source, 'private function form(')
    && str_contains($source, 'private function permissionTree(')
    && str_contains($source, 'private function userAssignment(')
    && ! str_contains($source, 'private function renderRoleView(');

if ($apply && ! $canApply) {
    $errors[] = 'Apply refused: expected source pattern was not found or renderRoleView already exists.';
}

$applied = false;
$backupPath = null;

if ($apply && $errors === []) {
    $backupPath = $controllerPath . '.phase-1.38-markup-view.bak';
    copy($controllerPath, $backupPath);

    $newSource = $source;
    $newSource = replaceMethod($newSource, 'index', indexMethod());
    $newSource = replaceMethod($newSource, 'form', formMethod());
    $newSource = replaceMethod($newSource, 'permissionTree', permissionTreeMethod());
    $newSource = replaceMethod($newSource, 'userAssignment', userAssignmentMethod());
    $newSource = insertBeforeMethod($newSource, 'e', renderRoleViewMethod());

    if ($newSource === $source) {
        $errors[] = 'Apply refused: source rewrite produced no changes.';
    } else {
        file_put_contents($controllerPath, $newSource);
        $applied = true;
        $actions[] = 'Replaced index, form, permissionTree, and userAssignment with view-backed methods.';
        $actions[] = 'Inserted renderRoleView helper.';
        $actions[] = 'Backup written to ' . $backupPath;
    }
}

$after = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
$afterSignals = markupSignals($after);

$report = [];
$report[] = '# RoleAdminController Markup View Cutover';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Mode: ' . ($apply ? 'apply' : 'read-only');
$report[] = 'Controller: ' . $controllerRelative;
$report[] = 'Can apply: ' . ($canApply ? 'yes' : 'no');
$report[] = 'Applied: ' . ($applied ? 'yes' : 'no');
$report[] = 'Backup: ' . ($backupPath ?? 'none');
$report[] = 'Errors: ' . count($errors);
$report[] = '';
$report[] = '## Before signals';
foreach ($beforeSignals as $name => $value) {
    $report[] = '- ' . $name . ': ' . ($value ? 'yes' : 'no');
}
$report[] = '';
$report[] = '## After signals';
foreach ($afterSignals as $name => $value) {
    $report[] = '- ' . $name . ': ' . ($value ? 'yes' : 'no');
}

if ($actions !== []) {
    $report[] = '';
    $report[] = '## Actions';
    foreach ($actions as $action) {
        $report[] = '- ' . $action;
    }
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
$log[] = 'Role admin markup view cutover report written to: ' . $reportPath;
$log[] = 'MODE ' . ($apply ? 'apply' : 'read-only');
$log[] = 'CAN_APPLY ' . ($canApply ? 'yes' : 'no');
$log[] = 'APPLIED ' . ($applied ? 'yes' : 'no');
$log[] = 'CUTOVER_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;

exit($errors === [] ? 0 : 1);

/** @return array<string,bool> */
function markupSignals(string $source): array
{
    return [
        'contains_form' => str_contains($source, '<form'),
        'contains_table' => str_contains($source, '<table'),
        'contains_input' => str_contains($source, '<input'),
        'contains_label' => str_contains($source, '<label'),
        'contains_heredoc' => str_contains($source, '<<<'),
    ];
}

function replaceMethod(string $source, string $method, string $replacement): string
{
    $range = methodRange($source, $method);
    if ($range === null) {
        throw new RuntimeException('Unable to find method: ' . $method);
    }
    return substr($source, 0, $range[0]) . $replacement . substr($source, $range[1]);
}

function insertBeforeMethod(string $source, string $method, string $insertion): string
{
    if (str_contains($source, 'private function renderRoleView(')) {
        return $source;
    }
    $range = methodRange($source, $method);
    if ($range === null) {
        throw new RuntimeException('Unable to find insertion point before method: ' . $method);
    }
    return substr($source, 0, $range[0]) . $insertion . PHP_EOL . PHP_EOL . substr($source, $range[0]);
}

/** @return array{0:int,1:int}|null */
function methodRange(string $source, string $method): ?array
{
    $pattern = '/(?:public|private|protected)\s+function\s+' . preg_quote($method, '/') . '\s*\([^)]*\)\s*(?::\s*[^\{]+)?\{/m';
    if (! preg_match($pattern, $source, $match, PREG_OFFSET_CAPTURE)) {
        return null;
    }
    $start = $match[0][1];
    $brace = strpos($source, '{', $start);
    if ($brace === false) {
        return null;
    }
    $depth = 0;
    $length = strlen($source);
    for ($i = $brace; $i < $length; $i++) {
        $char = $source[$i];
        if ($char === '{') {
            $depth++;
        } elseif ($char === '}') {
            $depth--;
            if ($depth === 0) {
                return [$start, $i + 1];
            }
        }
    }
    return null;
}

function indexMethod(): string
{
    return <<<'PHP'
public function index(Request $request): Response
    {
        $this->currentAdminUser();

        return $this->html('Roles & Permissions', $this->renderRoleView('index.php', [
            'roles' => $this->roles->allRoles(),
        ]));
    }
PHP;
}

function formMethod(): string
{
    return <<<'PHP'
private function form(string $action, ?array $role = null, ?string $error = null, array $submitted = []): string
    {
        $roleId = $role !== null ? (int) $role['id'] : null;
        $selectedPermissions = $submitted !== []
            ? $this->idsFromForm($submitted, 'permission_ids')
            : ($roleId !== null ? $this->roles->permissionIdsForRole($roleId) : []);
        $selectedUsers = $submitted !== []
            ? $this->idsFromForm($submitted, 'user_ids')
            : ($roleId !== null ? $this->roles->userIdsForRole($roleId) : []);

        return $this->renderRoleView('form.php', [
            'action' => $action,
            'csrfToken' => $this->csrf->token(),
            'code' => (string) ($submitted['code'] ?? $role['code'] ?? ''),
            'label' => (string) ($submitted['label'] ?? $role['label'] ?? ''),
            'error' => $error,
            'permissionTree' => $this->permissionTree($selectedPermissions),
            'userAssignment' => $this->userAssignment($selectedUsers),
        ]);
    }
PHP;
}

function permissionTreeMethod(): string
{
    return <<<'PHP'
private function permissionTree(array $selected): string
    {
        $groups = require dirname(__DIR__, 3) . '/zoosper-auth/config/acl.php';
        $tree = (new AclTreeBuilder())->build($this->roles->allPermissions(), is_array($groups) ? $groups : []);

        return $this->renderRoleView('permission-tree.php', [
            'tree' => $tree,
            'selected' => $selected,
        ]);
    }
PHP;
}

function userAssignmentMethod(): string
{
    return <<<'PHP'
private function userAssignment(array $selected): string
    {
        if ($this->users === null) {
            return 'User assignment requires AdminUserRepository injection.';
        }

        return $this->renderRoleView('user-assignment.php', [
            'users' => $this->users->allForAssignment(),
            'selected' => $selected,
        ]);
    }
PHP;
}

function renderRoleViewMethod(): string
{
    return <<<'PHP'
private function renderRoleView(string $template, array $data = []): string
    {
        $path = dirname(__DIR__, 2) . '/resources/views/admin/roles/' . ltrim($template, '/');
        if (!is_file($path)) {
            throw new RuntimeException('Role admin view not found: ' . $template);
        }

        $escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        extract($data, EXTR_SKIP);
        ob_start();
        require $path;
        return (string) ob_get_clean();
    }
PHP;
}
