<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$loginControllerPath = $basePath . '/app/zoosper-admin/src/Controller/LoginController.php';

print "Zoosper safe UserAdminController locale UI integration\n";
print "======================================================\n\n";

if (!is_file($controllerPath)) {
    fwrite(STDERR, "Missing UserAdminController: {$controllerPath}\n");
    exit(2);
}

$source = (string) file_get_contents($controllerPath);
$original = $source;

if (is_file($loginControllerPath)) {
    $loginSource = (string) file_get_contents($loginControllerPath);
    if (str_contains($loginSource, 'name="locale"') || str_contains($loginSource, "name='locale'")) {
        fwrite(STDERR, "LoginController contains a locale field. Apply the login-controller hotfix before continuing.\n");
        exit(2);
    }
}

if (str_contains($source, '$localeFieldHtml') && str_contains($source, 'renderAdminLocaleField(')) {
    print "- UserAdminController already appears to have safe locale UI integration\n";
    print "Result: OK\n";
    exit(0);
}

$source = add_locale_render_method($source);
$source = add_locale_field_variable_before_form_heredoc($source);
$source = insert_locale_placeholder_into_form($source);

if ($source === $original) {
    fwrite(STDERR, "No changes were made. Run tools/diagnose-safe-user-admin-locale-ui.php.\n");
    exit(2);
}

$backup = $controllerPath . '.phase-1.09.bak';
if (!is_file($backup)) {
    copy($controllerPath, $backup);
    print "- backup created: app/zoosper-admin/src/Controller/UserAdminController.php.phase-1.09.bak\n";
}

file_put_contents($controllerPath, $source);
print "- updated app/zoosper-admin/src/Controller/UserAdminController.php\n";
print "Result: OK\n";

function add_locale_render_method(string $source): string
{
    if (str_contains($source, 'function renderAdminLocaleField(')) {
        return $source;
    }

    $method = <<<'PHP_METHOD'

    /**
     * Renders the admin interface locale field for the user form.
     *
     * This method deliberately builds escaped HTML from PHP variables instead
     * of embedding raw PHP template tags inside controller-rendered heredoc.
     */
    private function renderAdminLocaleField(?string $currentLocale): string
    {
        $currentLocale = is_string($currentLocale) ? trim($currentLocale) : '';
        $blankSelected = $currentLocale === '' ? ' selected' : '';
        $enAuSelected = $currentLocale === 'en_AU' ? ' selected' : '';

        return implode("\n", [
            '<div class="admin-form-field admin-form-field--locale">',
            '    <label for="admin-user-locale">Admin interface locale</label>',
            '    <select id="admin-user-locale" name="locale">',
            '        <option value=""' . $blankSelected . '>Use configured admin locale</option>',
            '        <option value="en_AU"' . $enAuSelected . '>English (Australia)</option>',
            '    </select>',
            '    <small class="admin-form-help">Leave blank to use the configured admin locale.</small>',
            '</div>',
        ]);
    }
PHP_METHOD;

    $position = strrpos($source, "\n}");
    if ($position === false) {
        fwrite(STDERR, "Unable to find final class closing brace for render method insertion.\n");
        exit(2);
    }

    return substr($source, 0, $position) . $method . substr($source, $position);
}

function add_locale_field_variable_before_form_heredoc(string $source): string
{
    if (str_contains($source, '$localeFieldHtml = $this->renderAdminLocaleField(')) {
        return $source;
    }

    $matches = [];
    if (preg_match_all('/^\s*\$[A-Za-z_][A-Za-z0-9_]*\s*=\s*<<<[A-Z_]+/m', $source, $matches, PREG_OFFSET_CAPTURE) < 1) {
        fwrite(STDERR, "Could not find a heredoc assignment in UserAdminController.\n");
        exit(2);
    }

    foreach ($matches[0] as [$match, $offset]) {
        $after = substr($source, $offset, 3000);
        if (str_contains($after, '<form') && (str_contains($after, 'name="email"') || str_contains($after, "name='email'"))) {
            $lineStart = strrpos(substr($source, 0, $offset), "\n");
            $lineStart = $lineStart === false ? 0 : $lineStart + 1;
            $indent = preg_match('/^(\s*)/', substr($source, $lineStart, $offset - $lineStart), $indentMatches) === 1 ? $indentMatches[1] : '        ';
            $insert = $indent . '$localeFieldHtml = $this->renderAdminLocaleField($submitted[\'locale\'] ?? $user->locale ?? null);' . PHP_EOL;

            return substr($source, 0, $lineStart) . $insert . substr($source, $lineStart);
        }
    }

    fwrite(STDERR, "Could not find form heredoc with email field.\n");
    exit(2);
}

function insert_locale_placeholder_into_form(string $source): string
{
    if (str_contains($source, '{$localeFieldHtml}')) {
        return $source;
    }

    $patterns = [
        '/(\n\s*<label[^>]*>\s*Email\s*<\/label>.*?\n\s*<input[^>]+name=["\']email["\'][^>]*>)/is',
        '/(\n\s*<input[^>]+name=["\']email["\'][^>]*>)/is',
        '/(\n\s*<label[^>]*>\s*Password\s*<\/label>)/is',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $source, $match, PREG_OFFSET_CAPTURE) === 1) {
            $position = $match[0][1] + strlen($match[0][0]);
            $insert = PHP_EOL . '        {$localeFieldHtml}';

            return substr($source, 0, $position) . $insert . substr($source, $position);
        }
    }

    fwrite(STDERR, "Unable to insert locale field placeholder into form safely.\n");
    exit(2);
}
