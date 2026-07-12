<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$loginControllerPath = $basePath . '/app/zoosper-admin/src/Controller/LoginController.php';

print "Zoosper safe UserAdminController locale UI integration hotfix\n";
print "=============================================================\n\n";

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

$block = find_form_heredoc_block($source);
if ($block === null) {
    fwrite(STDERR, "Could not find a heredoc/nowdoc block containing the admin user form and email field.\n");
    fwrite(STDERR, "Run tools/diagnose-safe-user-admin-locale-ui.php and share the output.\n");
    exit(2);
}

$source = add_locale_render_method($source);
$source = add_locale_field_variable_before_block($source, $block);
$source = insert_locale_placeholder_into_block($source, $block);

if ($source === $original) {
    fwrite(STDERR, "No changes were made. Run tools/diagnose-safe-user-admin-locale-ui.php.\n");
    exit(2);
}

$backup = $controllerPath . '.phase-1.09.1.bak';
if (!is_file($backup)) {
    copy($controllerPath, $backup);
    print "- backup created: app/zoosper-admin/src/Controller/UserAdminController.php.phase-1.09.1.bak\n";
}

file_put_contents($controllerPath, $source);
print "- updated app/zoosper-admin/src/Controller/UserAdminController.php\n";
print "Result: OK\n";

/** @return array{start:int,end:int,openerLineStart:int,body:string,label:string}|null */
function find_form_heredoc_block(string $source): ?array
{
    if (preg_match_all('/<<<\s*(?:\'(?<slabel>[A-Za-z_][A-Za-z0-9_]*)\'|"(?<dlabel>[A-Za-z_][A-Za-z0-9_]*)"|(?<label>[A-Za-z_][A-Za-z0-9_]*))/m', $source, $matches, PREG_OFFSET_CAPTURE) < 1) {
        return null;
    }

    foreach ($matches[0] as $index => $match) {
        $label = $matches['label'][$index][0] ?: ($matches['slabel'][$index][0] ?: $matches['dlabel'][$index][0]);
        $openerOffset = $match[1];
        $lineEnd = strpos($source, "\n", $openerOffset);
        if ($lineEnd === false) {
            continue;
        }

        $terminatorPattern = '/^\s*' . preg_quote($label, '/') . '\s*;?\s*$/m';
        if (preg_match($terminatorPattern, $source, $endMatch, PREG_OFFSET_CAPTURE, $lineEnd + 1) !== 1) {
            continue;
        }

        $blockStart = $lineEnd + 1;
        $blockEnd = $endMatch[0][1];
        $body = substr($source, $blockStart, $blockEnd - $blockStart);
        if (str_contains($body, '<form') && (str_contains($body, 'name="email"') || str_contains($body, "name='email'"))) {
            $openerLineStart = strrpos(substr($source, 0, $openerOffset), "\n");
            $openerLineStart = $openerLineStart === false ? 0 : $openerLineStart + 1;

            return [
                'start' => $blockStart,
                'end' => $blockEnd,
                'openerLineStart' => $openerLineStart,
                'body' => $body,
                'label' => $label,
            ];
        }
    }

    return null;
}

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

/** @param array{openerLineStart:int} $block */
function add_locale_field_variable_before_block(string $source, array $block): string
{
    if (str_contains($source, '$localeFieldHtml = $this->renderAdminLocaleField(')) {
        return $source;
    }

    $line = substr($source, $block['openerLineStart'], strpos($source, "\n", $block['openerLineStart']) - $block['openerLineStart']);
    $indent = preg_match('/^(\s*)/', $line, $matches) === 1 ? $matches[1] : '        ';
    $insert = $indent . '$localeFieldHtml = $this->renderAdminLocaleField($submitted[\'locale\'] ?? $user->locale ?? null);' . PHP_EOL;

    return substr($source, 0, $block['openerLineStart']) . $insert . substr($source, $block['openerLineStart']);
}

/** @param array{start:int,end:int,body:string} $block */
function insert_locale_placeholder_into_block(string $source, array $block): string
{
    if (str_contains($source, '{$localeFieldHtml}')) {
        return $source;
    }

    $body = $block['body'];
    $patterns = [
        '/(<input[^>]+name=["\']email["\'][^>]*>)/is',
        '/(<label[^>]*>\s*Password\s*<\/label>)/is',
        '/(<label[^>]*>\s*Status\s*<\/label>)/is',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $body, $match, PREG_OFFSET_CAPTURE) === 1) {
            $relativePosition = $match[0][1] + strlen($match[0][0]);
            $absolutePosition = $block['start'] + $relativePosition;
            $insert = PHP_EOL . '        {$localeFieldHtml}';

            return substr($source, 0, $absolutePosition) . $insert . substr($source, $absolutePosition);
        }
    }

    fwrite(STDERR, "Unable to insert locale field placeholder into form block safely.\n");
    exit(2);
}
