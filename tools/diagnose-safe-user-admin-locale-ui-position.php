<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';

print "Zoosper safe UserAdminController locale UI position diagnostics\n";
print "===============================================================\n\n";

if (!is_file($controllerPath)) {
    print "UserAdminController: missing\n";
    exit(0);
}

$source = (string) file_get_contents($controllerPath);
$lines = preg_split('/\R/', $source) ?: [];
foreach ($lines as $index => $line) {
    if (str_contains($line, '{$localeFieldHtml}') || str_contains($line, '<label>Email') || str_contains($line, '<label>Name') || str_contains($line, 'name="locale"')) {
        print str_pad((string) ($index + 1), 5, ' ', STR_PAD_LEFT) . ': ' . trim($line) . PHP_EOL;
    }
}

print "\nhas_placeholder: " . (str_contains($source, '{$localeFieldHtml}') ? 'yes' : 'no') . PHP_EOL;
print "has_render_method: " . (str_contains($source, 'function renderAdminLocaleField(') ? 'yes' : 'no') . PHP_EOL;
print "placeholder_before_email: " . (preg_match('/\{\$localeFieldHtml\}\s*\n\s*<label>\s*Email\s*<input\b/i', $source) === 1 ? 'yes' : 'no') . PHP_EOL;
