<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$repositoryPath = find_file_containing($basePath, 'class AdminUserRepository');

print "Zoosper admin user locale persistence apply\n";
print "===========================================\n\n";

if (!is_file($controllerPath)) {
    fwrite(STDERR, "Missing UserAdminController: {$controllerPath}\n");
    exit(2);
}

if ($repositoryPath === null || !is_file($repositoryPath)) {
    fwrite(STDERR, "Missing AdminUserRepository.\n");
    exit(2);
}

$controllerChanged = patch_user_admin_controller($controllerPath);
$repositoryChanged = patch_admin_user_repository($repositoryPath);

print '- controller: ' . relative_path($basePath, $controllerPath) . ($controllerChanged ? ' updated' : ' already ok') . PHP_EOL;
print '- repository: ' . relative_path($basePath, $repositoryPath) . ($repositoryChanged ? ' updated' : ' already ok') . PHP_EOL;
print "Result: OK\n";

function patch_user_admin_controller(string $path): bool
{
    $source = (string) file_get_contents($path);
    $original = $source;

    if (!str_contains($source, 'function normaliseAdminLocale(')) {
        $source = add_normalise_locale_method($source);
    }

    if (!str_contains($source, "'locale' =>")) {
        $source = add_locale_to_submitted_array($source);
    }

    if (!str_contains($source, 'locale: $submitted[\'locale\']')) {
        $source = add_locale_to_admin_user_construction($source);
    }

    if ($source === $original) {
        return false;
    }

    backup_once($path, '.phase-1.11.bak');
    file_put_contents($path, $source);

    return true;
}

function add_normalise_locale_method(string $source): string
{
    $method = <<<'PHP_METHOD'

    /**
     * Normalises the submitted admin interface locale.
     *
     * Empty values intentionally become null, which means the admin user falls
     * back to the configured admin locale. Only strict xx_YY locale codes are
     * accepted so locale values cannot influence translation file paths.
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

    $position = strrpos($source, "\n}");
    if ($position === false) {
        fwrite(STDERR, "Unable to insert normaliseAdminLocale(): class closing brace not found.\n");
        exit(2);
    }

    return substr($source, 0, $position) . $method . substr($source, $position);
}

function add_locale_to_submitted_array(string $source): string
{
    $patterns = [
        "/('status'\s*=>\s*[^,\n]+,?)/",
        "/(\"status\"\s*=>\s*[^,\n]+,?)/",
        "/('email'\s*=>\s*[^,\n]+,?)/",
        "/(\"email\"\s*=>\s*[^,\n]+,?)/",
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $source, $matches, PREG_OFFSET_CAPTURE) === 1) {
            $position = $matches[0][1] + strlen($matches[0][0]);
            $indent = detect_line_indent($source, $matches[0][1]);
            $insert = PHP_EOL . $indent . "'locale' => \$this->normaliseAdminLocale(\$_POST['locale'] ?? null),";

            return substr($source, 0, $position) . $insert . substr($source, $position);
        }
    }

    fwrite(STDERR, "Unable to find submitted array insertion point for locale.\n");
    exit(2);
}

function add_locale_to_admin_user_construction(string $source): string
{
    if (!str_contains($source, 'new AdminUser(')) {
        return $source;
    }

    $call = find_call($source, 'new AdminUser(');
    if ($call === null) {
        fwrite(STDERR, "Unable to parse AdminUser constructor call in UserAdminController.\n");
        exit(2);
    }

    [$start, $end, $body] = $call;
    if (str_contains($body, 'locale:')) {
        return $source;
    }

    $trimmed = rtrim($body);
    $comma = str_ends_with(trim($trimmed), ',') ? '' : ',';
    $replacement = 'new AdminUser(' . $trimmed . $comma . "\n            locale: \$submitted['locale'] ?? null\n        )";

    return substr($source, 0, $start) . $replacement . substr($source, $end + 1);
}

function patch_admin_user_repository(string $path): bool
{
    $source = (string) file_get_contents($path);
    $original = $source;

    $source = patch_insert_locale($source);
    $source = patch_update_locale($source);
    $source = patch_params_locale($source);

    if ($source === $original) {
        return false;
    }

    backup_once($path, '.phase-1.11.bak');
    file_put_contents($path, $source);

    return true;
}

function patch_insert_locale(string $source): string
{
    if (preg_match('/INSERT\s+INTO\s+`?admin_users`?\s*\((?<columns>[^)]*)\)\s*VALUES\s*\((?<values>[^)]*)\)/is', $source, $matches, PREG_OFFSET_CAPTURE) !== 1) {
        return $source;
    }

    if (str_contains($matches['columns'][0], 'locale')) {
        return $source;
    }

    $columnsStart = $matches['columns'][1];
    $columnsEnd = $columnsStart + strlen($matches['columns'][0]);
    $valuesStart = $matches['values'][1];
    $valuesEnd = $valuesStart + strlen($matches['values'][0]);

    $source = substr($source, 0, $columnsEnd) . ', locale' . substr($source, $columnsEnd);
    $valuesStart += strlen(', locale');
    $valuesEnd += strlen(', locale');

    return substr($source, 0, $valuesEnd) . ', :locale' . substr($source, $valuesEnd);
}

function patch_update_locale(string $source): string
{
    if (preg_match('/UPDATE\s+`?admin_users`?\s+SET\s+(?<set>.*?)\s+WHERE\s+/is', $source, $matches, PREG_OFFSET_CAPTURE) !== 1) {
        return $source;
    }

    if (str_contains($matches['set'][0], 'locale')) {
        return $source;
    }

    $setEnd = $matches['set'][1] + strlen($matches['set'][0]);

    return substr($source, 0, $setEnd) . ', locale = :locale' . substr($source, $setEnd);
}

function patch_params_locale(string $source): string
{
    if (str_contains($source, "'locale' =>")) {
        return $source;
    }

    $patterns = [
        "/('status'\s*=>\s*[^,\n]+,?)/",
        "/(\"status\"\s*=>\s*[^,\n]+,?)/",
        "/('email'\s*=>\s*[^,\n]+,?)/",
        "/(\"email\"\s*=>\s*[^,\n]+,?)/",
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $source, $matches, PREG_OFFSET_CAPTURE) === 1) {
            $position = $matches[0][1] + strlen($matches[0][0]);
            $indent = detect_line_indent($source, $matches[0][1]);
            $insert = PHP_EOL . $indent . "'locale' => \$user->locale,";

            return substr($source, 0, $position) . $insert . substr($source, $position);
        }
    }

    return $source;
}

/** @return array{int,int,string}|null */
function find_call(string $source, string $needle): ?array
{
    $start = strpos($source, $needle);
    if ($start === false) {
        return null;
    }

    $open = $start + strlen($needle) - 1;
    $depth = 0;
    $length = strlen($source);
    for ($i = $open; $i < $length; $i++) {
        $char = $source[$i];
        if ($char === '(') {
            $depth++;
        } elseif ($char === ')') {
            $depth--;
            if ($depth === 0) {
                return [$start, $i, substr($source, $open + 1, $i - $open - 1)];
            }
        }
    }

    return null;
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
