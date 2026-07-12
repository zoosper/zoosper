<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin user locale hydration apply hotfix\n";
print "================================================\n\n";

$modelPath = find_admin_user_model($basePath);
if ($modelPath === null) {
    fwrite(STDERR, "Unable to locate concrete AdminUser model. Run tools/diagnose-admin-user-locale-hydration.php.\n");
    exit(2);
}

$hydrationPath = find_admin_user_hydration($basePath, $modelPath);
if ($hydrationPath === null) {
    fwrite(STDERR, "Unable to locate AdminUser hydration code. Run tools/diagnose-admin-user-locale-hydration.php.\n");
    exit(2);
}

$modelChanged = patch_admin_user_model($modelPath);
$hydrationChanged = patch_admin_user_hydration($hydrationPath);

print '- model: ' . relative_path($basePath, $modelPath) . ($modelChanged ? ' updated' : ' already ok') . PHP_EOL;
print '- hydration: ' . relative_path($basePath, $hydrationPath) . ($hydrationChanged ? ' updated' : ' already ok') . PHP_EOL;
print "\nResult: OK\n";

function find_admin_user_model(string $basePath): ?string
{
    $matches = php_files($basePath, static function (string $path, string $contents): bool {
        return basename($path) === 'AdminUser.php'
            && preg_match('/\bclass\s+AdminUser\b/', $contents) === 1
            && !str_contains($contents, 'class AdminUserLocaleResolver');
    });

    return $matches[0] ?? null;
}

function find_admin_user_hydration(string $basePath, string $modelPath): ?string
{
    $matches = php_files($basePath, static function (string $path, string $contents) use ($modelPath): bool {
        return $path !== $modelPath
            && str_contains($contents, 'new AdminUser(')
            && !str_contains($contents, 'class AdminUserLocaleResolver');
    });

    usort($matches, static function (string $a, string $b): int {
        $score = static fn (string $path): int => str_contains($path, 'Repository') ? 0 : (str_contains($path, 'Service') ? 1 : 2);

        return $score($a) <=> $score($b) ?: strcmp($a, $b);
    });

    return $matches[0] ?? null;
}

/** @return list<string> */
function php_files(string $basePath, callable $filter): array
{
    $results = [];
    foreach ([$basePath . '/app', $basePath . '/modules'] as $root) {
        if (!is_dir($root)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            $path = $file->getPathname();
            $contents = (string) file_get_contents($path);
            if ($filter($path, $contents)) {
                $results[] = $path;
            }
        }
    }

    return $results;
}

function patch_admin_user_model(string $path): bool
{
    $source = (string) file_get_contents($path);
    if (preg_match('/\$locale\b/', $source) === 1) {
        return false;
    }

    $updated = preg_replace(
        '/(public\s+function\s+__construct\s*\((?:(?!\)\s*\{).)*)(\n\s*\)\s*\{)/s',
        "$1\n        public ?string \$locale = null,$2",
        $source,
        1
    );

    if (!is_string($updated) || $updated === $source) {
        $updated = preg_replace(
            '/(final\s+(?:readonly\s+)?class\s+AdminUser\b[^\{]*\{)/',
            "$1\n    public ?string \$locale = null;\n",
            $source,
            1
        );
    }

    if (!is_string($updated) || $updated === $source) {
        fwrite(STDERR, "Unable to patch AdminUser model constructor/property in {$path}.\n");
        exit(2);
    }

    backup_once($path, '.phase-1.03.1.bak');
    file_put_contents($path, $updated);

    return true;
}

function patch_admin_user_hydration(string $path): bool
{
    $source = (string) file_get_contents($path);
    if (str_contains($source, 'locale:') && str_contains($source, "['locale']")) {
        return false;
    }

    $call = find_constructor_call($source, 'new AdminUser(');
    if ($call === null) {
        fwrite(STDERR, "Unable to find AdminUser constructor call in {$path}.\n");
        exit(2);
    }

    [$start, $end, $body] = $call;
    $rowVariable = detect_row_variable($body) ?? '$row';
    $localeExpression = "isset({$rowVariable}['locale']) && is_string({$rowVariable}['locale']) && trim({$rowVariable}['locale']) !== '' ? trim({$rowVariable}['locale']) : null";
    $trimmed = rtrim($body);
    $comma = str_ends_with($trimmed, ',') ? '' : ',';
    $replacement = substr($source, $start, strlen('new AdminUser(')) . $trimmed . $comma . "\n            locale: {$localeExpression}\n        )";
    $updated = substr($source, 0, $start) . $replacement . substr($source, $end + 1);
    $updated = patch_select_columns($updated);

    backup_once($path, '.phase-1.03.1.bak');
    file_put_contents($path, $updated);

    return true;
}

/** @return array{int,int,string}|null */
function find_constructor_call(string $source, string $needle): ?array
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

function detect_row_variable(string $body): ?string
{
    if (preg_match('/\$(row|record|data|user|adminUser)\s*\[/', $body, $matches) === 1) {
        return '$' . $matches[1];
    }

    if (preg_match('/\$([A-Za-z_][A-Za-z0-9_]*)\s*\[/', $body, $matches) === 1) {
        return '$' . $matches[1];
    }

    return null;
}

function patch_select_columns(string $source): string
{
    if (preg_match('/SELECT\s+\*/i', $source) === 1 || preg_match('/\blocale\b/', $source) === 1) {
        return $source;
    }

    return preg_replace('/SELECT\s+(.+?)\s+FROM\s+`?admin_users`?/is', 'SELECT $1, locale FROM admin_users', $source, 1) ?? $source;
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
