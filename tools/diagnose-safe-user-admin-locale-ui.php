<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';

print "Zoosper safe UserAdminController locale UI diagnostics\n";
print "======================================================\n\n";

if (!is_file($controllerPath)) {
    print "UserAdminController: missing\n";
    exit(0);
}

$source = (string) file_get_contents($controllerPath);
print 'uses_heredoc: ' . (preg_match('/<<<\s*(?:\'?[A-Za-z_][A-Za-z0-9_]*\'?|"[A-Za-z_][A-Za-z0-9_]*")/', $source) === 1 ? 'yes' : 'no') . PHP_EOL;
print 'has_form: ' . (str_contains($source, '<form') ? 'yes' : 'no') . PHP_EOL;
print 'has_email_field: ' . ((str_contains($source, 'name="email"') || str_contains($source, "name='email'")) ? 'yes' : 'no') . PHP_EOL;
print 'has_locale_placeholder: ' . (str_contains($source, '{$localeFieldHtml}') ? 'yes' : 'no') . PHP_EOL;
print 'has_render_method: ' . (str_contains($source, 'function renderAdminLocaleField(') ? 'yes' : 'no') . PHP_EOL;
print 'has_raw_template_tag_after_opening_php: ' . (contains_embedded_template_tag($source) ? 'yes' : 'no') . PHP_EOL;
print "\nHeredoc openers:\n";
foreach (heredoc_openers($source) as $opener) {
    print '- ' . $opener . PHP_EOL;
}

function contains_embedded_template_tag(string $source): bool
{
    $withoutOpeningTag = preg_replace('/^\s*<\?php\s*/', '', $source, 1) ?? $source;

    return str_contains($withoutOpeningTag, '<?=') || str_contains($withoutOpeningTag, '<?php');
}

/** @return list<string> */
function heredoc_openers(string $source): array
{
    if (preg_match_all('/^.*<<<.*$/m', $source, $matches) < 1) {
        return [];
    }

    return array_map('trim', $matches[0]);
}
