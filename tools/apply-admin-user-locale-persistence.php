<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$repositoryPath = find_file_containing($basePath, 'class AdminUserRepository');

print "Zoosper admin user locale post-save persistence hotfix\n";
print "======================================================\n\n";

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

function patch_controller(string $path): bool
{
    $source = (string) file_get_contents($path);
    $original = $source;
    $repositoryProperty = detect_repository_property($source);

    if ($repositoryProperty === null) {
        fwrite(STDERR, "Unable to detect AdminUserRepository property in UserAdminController.\n");
        exit(2);
    }

    if (!str_contains($source, 'function normaliseAdminLocale(')) {
        $source = add_normalise_locale_method($source);
    }

    if (!str_contains($source, 'function persistAdminUserLocalePreference(')) {
        $source = add_persist_locale_method($source, $repositoryProperty);
    }

    if (!str_contains($source, 'persistAdminUserLocalePreference(')) {
        $source = add_post_save_call($source, $repositoryProperty);
    } elseif (substr_count($source, 'persistAdminUserLocalePreference(') < 2) {
        // One occurrence means method declaration only; add usage.
        $source = add_post_save_call($source, $repositoryProperty);
    }

    if ($source === $original) {
        return false;
    }

    backup_once($path, '.phase-1.11.2.bak');
    file_put_contents($path, $source);

    return true;
}

function detect_repository_property(string $source): ?string
{
    $patterns = [
        '/private\s+(?:readonly\s+)?AdminUserRepository\s+\$([A-Za-z_][A-Za-z0-9_]*)/',
        '/public\s+function\s+__construct\s*\([^)]*AdminUserRepository\s+\$([A-Za-z_][A-Za-z0-9_]*)/s',
        '/\$this->([A-Za-z_][A-Za-z0-9_]*)->(?:save|update|create)\s*\(/',
        '/\$this->([A-Za-z_][A-Za-z0-9_]*)->find(?:ById)?\s*\(/',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $source, $matches) === 1) {
            return $matches[1];
        }
    }

    return null;
}

function add_normalise_locale_method(string $source): string
{
    $method = <<<'PHP_METHOD'

    /**
     * Normalises the submitted admin interface locale.
     *
     * Empty values become null so the user falls back to the configured admin
     * locale. Only safe xx_YY locale codes are persisted.
     */
    private function normaliseAdminLocale(mixed $locale): ?string
    {
        if (!is_string($locale)) {
            return null;
        }

        $locale = trim($locale);
        if ($locale === '') {
            return null;
        }

        return preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale) === 1 ? $locale : null;
    }
PHP_METHOD;

    return insert_method_before_class_end($source, $method);
}

function add_persist_locale_method(string $source, string $repositoryProperty): string
{
    $method = <<<PHP_METHOD

    /**
     * Persists the admin interface locale after the main user save succeeds.
     *
     * The existing user save flow remains untouched; this performs a small,
     * targeted locale update for edit screens where the user id is available.
     */
    private function persistAdminUserLocalePreference(object \$user): void
    {
        if (!property_exists(\$user, 'id') || \$user->id === null) {
            return;
        }

        \$this->{$repositoryProperty}->updateLocale((int) \$user->id, \$this->normaliseAdminLocale(\$_POST['locale'] ?? null));
    }
PHP_METHOD;

    return insert_method_before_class_end($source, $method);
}

function add_post_save_call(string $source, string $repositoryProperty): string
{
    $patterns = [
        '/(\$this->' . preg_quote($repositoryProperty, '/') . '->(?:save|update)\s*\(\s*\$user\s*\)\s*;)/',
        '/(\$this->' . preg_quote($repositoryProperty, '/') . '->(?:save|update)\s*\([^;]*\)\s*;)/',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $source, $matches, PREG_OFFSET_CAPTURE) === 1) {
            $insert = $matches[1][0] . PHP_EOL . detect_line_indent($source, $matches[1][1]) . '$this->persistAdminUserLocalePreference($user);';
            return substr($source, 0, $matches[1][1]) . $insert . substr($source, $matches[1][1] + strlen($matches[1][0]));
        }
    }

    fwrite(STDERR, "Unable to locate the repository save/update call for post-save locale persistence.\n");
    exit(2);
}

function patch_repository(string $path): bool
{
    $source = (string) file_get_contents($path);
    $original = $source;

    if (str_contains($source, 'function updateLocale(')) {
        return false;
    }

    $pdoProperty = detect_pdo_property($source);
    if ($pdoProperty === null) {
        fwrite(STDERR, "Unable to detect PDO property in AdminUserRepository.\n");
        exit(2);
    }

    $method = <<<PHP_METHOD

    /**
     * Updates only the admin interface locale for an existing admin user.
     *
     * A null locale intentionally means the configured admin locale should be
     * used. The caller is responsible for validating the locale format.
     */
    public function updateLocale(int \$id, ?string \$locale): void
    {
        \$statement = \$this->{$pdoProperty}->prepare('UPDATE admin_users SET locale = :locale WHERE id = :id');
        \$statement->execute([
            'locale' => \$locale,
            'id' => \$id,
        ]);
    }
PHP_METHOD;

    $source = insert_method_before_class_end($source, $method);

    if ($source === $original) {
        return false;
    }

    backup_once($path, '.phase-1.11.2.bak');
    file_put_contents($path, $source);

    return true;
}

function detect_pdo_property(string $source): ?string
{
    $patterns = [
        '/\$this->([A-Za-z_][A-Za-z0-9_]*)->prepare\s*\(/',
        '/private\s+(?:readonly\s+)?PDO\s+\$([A-Za-z_][A-Za-z0-9_]*)/',
        '/public\s+function\s+__construct\s*\([^)]*PDO\s+\$([A-Za-z_][A-Za-z0-9_]*)/s',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $source, $matches) === 1) {
            return $matches[1];
        }
    }

    return null;
}

function insert_method_before_class_end(string $source, string $method): string
{
    $position = strrpos($source, "\n}");
    if ($position === false) {
        fwrite(STDERR, "Unable to find final class closing brace.\n");
        exit(2);
    }

    return substr($source, 0, $position) . $method . substr($source, $position);
}

function detect_line_indent(string $source, int $offset): string
{
    $lineStart = strrpos(substr($source, 0, $offset), "\n");
    $lineStart = $lineStart === false ? 0 : $lineStart + 1;
    $line = substr($source, $lineStart, $offset - $lineStart);
    return preg_match('/^(\s*)/', $line, $matches) === 1 ? $matches[1] : '        ';
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
