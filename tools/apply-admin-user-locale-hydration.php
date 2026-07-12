<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin user locale hydration apply\n";
print "========================================\n\n";

$modelPath = find_file_containing($basePath, 'class AdminUser');
if ($modelPath === null) {
    fwrite(STDERR, "Unable to locate AdminUser model.\n");
    exit(2);
}

$repositoryPath = find_file_containing($basePath, 'new AdminUser(');
if ($repositoryPath === null) {
    fwrite(STDERR, "Unable to locate AdminUser hydration code.\n");
    exit(2);
}

$modelChanged = patch_admin_user_model($modelPath);
$repositoryChanged = patch_admin_user_hydration($repositoryPath);

print '- model: ' . relative_path($basePath, $modelPath) . ($modelChanged ? ' updated' : ' already ok') . PHP_EOL;
print '- hydration: ' . relative_path($basePath, $repositoryPath) . ($repositoryChanged ? ' updated' : ' already ok') . PHP_EOL;
print "\nResult: OK\n";

function find_file_containing(string $basePath, string $needle): ?string
{
    $roots = [$basePath . '/app', $basePath . '/modules'];
    foreach ($roots as $root) {
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
            if (str_contains($contents, $needle)) {
                return $path;
            }
        }
    }

    return null;
}

function patch_admin_user_model(string $path): bool
{
    $source = (string) file_get_contents($path);
    if (str_contains($source, 'locale')) {
        return false;
    }

    $updated = preg_replace(
        '/(public function __construct\s*\((?:(?!\)\s*\{).)*)(\n\s*\)\s*\{)/s',
        "$1\n        public ?string \$locale = null,$2",
        $source,
        1
    );

    if (!is_string($updated) || $updated === $source) {
        fwrite(STDERR, "Unable to patch AdminUser constructor in {$path}.\n");
        exit(2);
    }

    backup_once($path, '.phase-1.03.bak');
    file_put_contents($path, $updated);

    return true;
}

function patch_admin_user_hydration(string $path): bool
{
    $source = (string) file_get_contents($path);
    if (str_contains($source, "'locale'") || str_contains($source, 'locale:')) {
        return false;
    }

    $updated = preg_replace(
        '/new AdminUser\s*\((.*?)\)/s',
        static function (array $matches): string {
            $args = rtrim($matches[1]);
            $suffix = str_ends_with(trim($args), ',') ? '' : ',';

            return 'new AdminUser(' . $args . $suffix . "\n            locale: isset(\$row['locale']) && is_string(\$row['locale']) && trim(\$row['locale']) !== '' ? trim(\$row['locale']) : null\n        )";
        },
        $source,
        1
    );

    if (!is_string($updated) || $updated === $source) {
        fwrite(STDERR, "Unable to patch AdminUser hydration in {$path}.\n");
        exit(2);
    }

    $updated = patch_select_columns($updated);
    backup_once($path, '.phase-1.03.bak');
    file_put_contents($path, $updated);

    return true;
}

function patch_select_columns(string $source): string
{
    if (str_contains($source, 'locale')) {
        return $source;
    }

    return preg_replace('/SELECT\s+([^\n]+?)\s+FROM\s+`?admin_users`?/i', 'SELECT $1, locale FROM admin_users', $source, 1) ?? $source;
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
