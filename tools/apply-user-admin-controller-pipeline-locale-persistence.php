<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$repositoryPath = find_file_containing($basePath, 'class AdminUserRepository');

print "Zoosper UserAdminController pipeline locale persistence apply\n";
print "=============================================================\n\n";

if (!is_file($controllerPath)) {
    fwrite(STDERR, "Missing UserAdminController: {$controllerPath}\n");
    exit(2);
}

if ($repositoryPath === null || !is_file($repositoryPath)) {
    fwrite(STDERR, "Missing AdminUserRepository.\n");
    exit(2);
}

$repositoryChanged = patch_repository($repositoryPath);
$controllerChanged = patch_controller($controllerPath);

print '- controller: ' . relative_path($basePath, $controllerPath) . ($controllerChanged ? ' updated' : ' already ok') . PHP_EOL;
print '- repository: ' . relative_path($basePath, $repositoryPath) . ($repositoryChanged ? ' updated' : ' already ok') . PHP_EOL;
print "Result: OK\n";

function patch_repository(string $path): bool
{
    $source = (string) file_get_contents($path);
    $original = $source;

    $source = patch_create_with_role_ids($source);
    $source = patch_update_user($source);

    if ($source === $original) {
        return false;
    }

    backup_once($path, '.phase-1.17.bak');
    file_put_contents($path, $source);

    return true;
}

function patch_create_with_role_ids(string $source): string
{
    $source = preg_replace(
        '/public function createWithRoleIds\(([^)]*array \$roleIds)(?!, \?string \$locale)/',
        'public function createWithRoleIds($1, ?string $locale = null',
        $source,
        1
    ) ?? $source;

    $source = preg_replace(
        "/INSERT INTO admin_users \(email, name, password_hash, status, created_at, updated_at\) VALUES \(:email, :name, :password_hash, :status, :created_at, :updated_at\)/",
        'INSERT INTO admin_users (email, name, password_hash, status, locale, created_at, updated_at) VALUES (:email, :name, :password_hash, :status, :locale, :created_at, :updated_at)',
        $source,
        1
    ) ?? $source;

    $source = add_param_after($source, "'status' => \$status", "'locale' => \$locale");

    return $source;
}

function patch_update_user(string $source): string
{
    $source = preg_replace(
        '/public function updateUser\(([^)]*array \$roleIds)(?!, \?string \$locale)/',
        'public function updateUser($1, ?string $locale = null',
        $source,
        1
    ) ?? $source;

    $source = preg_replace(
        "/UPDATE admin_users SET email = :email, name = :name, status = :status, updated_at = :updated_at WHERE id = :id/",
        'UPDATE admin_users SET email = :email, name = :name, status = :status, locale = :locale, updated_at = :updated_at WHERE id = :id',
        $source,
        1
    ) ?? $source;

    $source = add_param_after($source, "'status' => \$status", "'locale' => \$locale");

    return $source;
}

function add_param_after(string $source, string $needle, string $insert): string
{
    if (str_contains($source, $insert)) {
        return $source;
    }

    $position = strpos($source, $needle);
    if ($position === false) {
        return $source;
    }

    $end = $position + strlen($needle);
    $lineIndent = detect_line_indent($source, $position);

    return substr($source, 0, $end) . ',' . PHP_EOL . $lineIndent . $insert . substr($source, $end);
}

function patch_controller(string $path): bool
{
    $source = (string) file_get_contents($path);
    $original = $source;

    if (!str_contains($source, 'function adminUserLocaleFromForm(')) {
        $source = add_locale_helper($source);
    }

    $source = patch_user_call($source, 'createWithRoleIds');
    $source = patch_user_call($source, 'updateUser');

    if ($source === $original) {
        return false;
    }

    backup_once($path, '.phase-1.17.bak');
    file_put_contents($path, $source);

    return true;
}

function add_locale_helper(string $source): string
{
    $method = <<<'PHP_METHOD'

    /**
     * Normalises the submitted admin interface locale through the AdminUser save pipeline.
     *
     * This keeps controller locale handling aligned with the field-definition
     * write map used by modular AdminUser save flows.
     *
     * @param array<string, mixed> $form
     */
    private function adminUserLocaleFromForm(array $form): ?string
    {
        $data = (new \Zoosper\Auth\Entity\Save\AdminUserSaveDataFactory())->fromSubmitted($form);
        $locale = $data->getData('locale');

        return is_string($locale) && trim($locale) !== '' ? trim($locale) : null;
    }
PHP_METHOD;

    return insert_method_before($source, 'private function roleIdsFromForm', $method);
}

function patch_user_call(string $source, string $method): string
{
    $offset = 0;
    while (($start = strpos($source, '->' . $method . '(', $offset)) !== false) {
        $open = strpos($source, '(', $start);
        if ($open === false) {
            break;
        }

        $end = matching_paren($source, $open);
        if ($end === null) {
            $offset = $start + 1;
            continue;
        }

        $body = substr($source, $open + 1, $end - $open - 1);
        if (str_contains($body, 'adminUserLocaleFromForm(')) {
            $offset = $end + 1;
            continue;
        }

        $formVar = detect_form_variable($body);
        if ($formVar === null) {
            $offset = $end + 1;
            continue;
        }

        $trimmed = rtrim($body);
        $comma = str_ends_with(trim($trimmed), ',') ? '' : ',';
        $indent = detect_call_argument_indent($body);
        $replacementBody = $trimmed . $comma . PHP_EOL . $indent . '$this->adminUserLocaleFromForm($' . $formVar . ')';
        $source = substr($source, 0, $open + 1) . $replacementBody . substr($source, $end);
        $offset = $open + 1 + strlen($replacementBody);
    }

    return $source;
}

function detect_form_variable(string $body): ?string
{
    if (preg_match('/\$([A-Za-z_][A-Za-z0-9_]*)\s*\[\s*[\'\"](?:email|name|status|locale)[\'\"]\s*\]/', $body, $matches) === 1) {
        return $matches[1];
    }

    if (preg_match('/\$([A-Za-z_][A-Za-z0-9_]*)\s*,\s*\$roleIds/s', $body, $matches) === 1) {
        return $matches[1];
    }

    return null;
}

function matching_paren(string $source, int $open): ?int
{
    $depth = 0;
    $length = strlen($source);
    $quote = null;
    for ($i = $open; $i < $length; $i++) {
        $char = $source[$i];
        if ($quote !== null) {
            if ($char === '\\') {
                $i++;
                continue;
            }
            if ($char === $quote) {
                $quote = null;
            }
            continue;
        }
        if ($char === '\'' || $char === '"') {
            $quote = $char;
            continue;
        }
        if ($char === '(') {
            $depth++;
        } elseif ($char === ')') {
            $depth--;
            if ($depth === 0) {
                return $i;
            }
        }
    }

    return null;
}

function insert_method_before(string $source, string $needle, string $method): string
{
    $position = strpos($source, $needle);
    if ($position !== false) {
        $lineStart = strrpos(substr($source, 0, $position), "\n");
        $lineStart = $lineStart === false ? $position : $lineStart + 1;

        return substr($source, 0, $lineStart) . $method . PHP_EOL . substr($source, $lineStart);
    }

    $classEnd = strrpos($source, "\n}");
    if ($classEnd === false) {
        fwrite(STDERR, "Unable to find class closing brace for helper insertion.\n");
        exit(2);
    }

    return substr($source, 0, $classEnd) . $method . substr($source, $classEnd);
}

function detect_call_argument_indent(string $body): string
{
    $lines = preg_split('/\R/', $body) ?: [];
    for ($i = count($lines) - 1; $i >= 0; $i--) {
        if (trim($lines[$i]) !== '') {
            return preg_match('/^(\s*)/', $lines[$i], $matches) === 1 ? $matches[1] : '            ';
        }
    }

    return '            ';
}

function detect_line_indent(string $source, int $offset): string
{
    $lineStart = strrpos(substr($source, 0, $offset), "\n");
    $lineStart = $lineStart === false ? 0 : $lineStart + 1;
    $line = substr($source, $lineStart, $offset - $lineStart);

    return preg_match('/^(\s*)/', $line, $matches) === 1 ? $matches[1] : '            ';
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

function backup_once(string $path, string $suffix): void
{
    $backup = $path . $suffix;
    if (!is_file($backup)) {
        copy($path, $backup);
    }
}

function relative_path(string $basePath, string $path): string
{
    return str_starts_with($path, $basePath . '/') ? substr($path, strlen($basePath) + 1) : $path;
}
