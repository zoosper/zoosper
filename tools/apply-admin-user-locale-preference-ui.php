<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin user locale preference UI apply\n";
print "=============================================\n\n";

$target = find_admin_user_form_target($basePath);
if ($target === null) {
    fwrite(STDERR, "Unable to locate an admin-user form target safely. Run tools/diagnose-admin-user-locale-preference-ui.php.\n");
    exit(2);
}

$source = (string) file_get_contents($target);
if (str_contains($source, 'name="locale"') || str_contains($source, "name='locale'")) {
    print '- locale field already appears in ' . relative_path($basePath, $target) . PHP_EOL;
    print "Result: OK\n";
    exit(0);
}

$insert = <<<'PHP_SNIPPET'

        <div class="admin-form-field admin-form-field--locale">
            <label for="admin-user-locale">Admin interface locale</label>
            <select id="admin-user-locale" name="locale">
                <option value="">Use configured admin locale</option>
                <option value="en_AU" <?= (($submitted['locale'] ?? $user->locale ?? '') === 'en_AU') ? 'selected' : '' ?>>English (Australia)</option>
            </select>
            <small class="admin-form-help">Leave blank to use the configured admin locale.</small>
        </div>
PHP_SNIPPET;

$updated = insert_after_email_field($source, $insert);
if ($updated === null) {
    fwrite(STDERR, "Unable to find a safe email-field insertion point in " . relative_path($basePath, $target) . ".\n");
    exit(2);
}

backup_once($target, '.phase-1.06.bak');
file_put_contents($target, $updated);
print '- updated ' . relative_path($basePath, $target) . PHP_EOL;
print "Result: OK\n";

function find_admin_user_form_target(string $basePath): ?string
{
    $candidates = [];
    foreach (php_files($basePath) as $path) {
        $contents = (string) file_get_contents($path);
        if (!str_contains($contents, 'AdminUser') && !str_contains($contents, 'admin user') && !str_contains($contents, 'Admin user')) {
            continue;
        }

        if ((str_contains($contents, 'name="email"') || str_contains($contents, "name='email'"))
            && (str_contains($contents, '<form') || str_contains($contents, 'form'))) {
            $candidates[] = $path;
        }
    }

    usort($candidates, static function (string $a, string $b): int {
        $score = static fn (string $path): int => str_contains($path, 'Controller') ? 0 : (str_contains($path, 'View') ? 1 : 2);

        return $score($a) <=> $score($b) ?: strcmp($a, $b);
    });

    return $candidates[0] ?? null;
}

/** @return list<string> */
function php_files(string $basePath): array
{
    $results = [];
    foreach ([$basePath . '/app', $basePath . '/modules'] as $root) {
        if (!is_dir($root)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->getExtension() === 'php') {
                $results[] = $file->getPathname();
            }
        }
    }

    return $results;
}

function insert_after_email_field(string $source, string $insert): ?string
{
    $patterns = [
        '/(<[^>]+name=["\']email["\'][^>]*>\s*<\/[^>]+>)/is',
        '/(<input[^>]+name=["\']email["\'][^>]*>)/is',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $source, $match, PREG_OFFSET_CAPTURE) === 1) {
            $position = $match[0][1] + strlen($match[0][0]);

            return substr($source, 0, $position) . $insert . substr($source, $position);
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
