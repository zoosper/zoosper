<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$loginControllerPath = $basePath . '/app/zoosper-admin/src/Controller/LoginController.php';

print "Zoosper safe UserAdminController locale UI position hotfix\n";
print "==========================================================\n\n";

if (!is_file($controllerPath)) {
    fwrite(STDERR, "Missing UserAdminController: {$controllerPath}\n");
    exit(2);
}

$source = (string) file_get_contents($controllerPath);
$original = $source;

if (is_file($loginControllerPath)) {
    $loginSource = (string) file_get_contents($loginControllerPath);
    if (str_contains($loginSource, 'name="locale"') || str_contains($loginSource, "name='locale'")) {
        fwrite(STDERR, "LoginController contains a locale field. Remove that before continuing.\n");
        exit(2);
    }
}

if (!str_contains($source, 'function renderAdminLocaleField(')) {
    fwrite(STDERR, "renderAdminLocaleField() is missing. Apply Phase 1.09.1 first.\n");
    exit(2);
}

$source = remove_locale_placeholder($source);
$source = insert_locale_placeholder_before_email_label($source);

if ($source === $original) {
    print "- locale placeholder already appears to be correctly positioned\n";
    print "Result: OK\n";
    exit(0);
}

$backup = $controllerPath . '.phase-1.09.2.bak';
if (!is_file($backup)) {
    copy($controllerPath, $backup);
    print "- backup created: app/zoosper-admin/src/Controller/UserAdminController.php.phase-1.09.2.bak\n";
}

file_put_contents($controllerPath, $source);
print "- moved locale placeholder to a safe position before the Email label\n";
print "Result: OK\n";

function remove_locale_placeholder(string $source): string
{
    // Remove standalone heredoc placeholder lines, including excess blank lines introduced by prior attempts.
    $source = preg_replace('/\n\s*\{\$localeFieldHtml\}\s*\n/', "\n", $source) ?? $source;

    // Defensive cleanup if the placeholder was inserted inline in a damaged location.
    return str_replace('{$localeFieldHtml}', '', $source);
}

function insert_locale_placeholder_before_email_label(string $source): string
{
    $patterns = [
        '/\n(?<indent>\s*)<label>\s*Email\s*<input\b/i',
        '/\n(?<indent>\s*)<label[^>]*>\s*Email\s*<input\b/i',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $source, $matches, PREG_OFFSET_CAPTURE) === 1) {
            $position = $matches[0][1];
            $indent = $matches['indent'][0] !== '' ? $matches['indent'][0] : '    ';
            $insert = PHP_EOL . $indent . '{$localeFieldHtml}';

            return substr($source, 0, $position) . $insert . substr($source, $position);
        }
    }

    fwrite(STDERR, "Unable to find the Email label insertion point. Run diagnose tool and attach output.\n");
    exit(2);
}
