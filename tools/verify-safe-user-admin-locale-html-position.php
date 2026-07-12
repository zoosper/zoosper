<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$loginPath = $basePath . '/app/zoosper-admin/src/Controller/LoginController.php';

print "Zoosper safe UserAdminController locale HTML position verification\n";
print "==================================================================\n\n";

$source = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
$login = is_file($loginPath) ? (string) file_get_contents($loginPath) : '';
$withoutOpeningTag = preg_replace('/^\s*<\?php\s*/', '', $source, 1) ?? $source;

$checks = [
    'UserAdminController exists' => is_file($controllerPath),
    'LoginController has no locale field' => !str_contains($login, 'name="locale"') && !str_contains($login, "name='locale'"),
    'renderAdminLocaleField method exists' => str_contains($source, 'function renderAdminLocaleField('),
    'locale placeholder exists' => str_contains($source, '{$localeFieldHtml}'),
    'locale placeholder is before Email label' => preg_match('/\{\$localeFieldHtml\}\s*\n\s*<label(?:[^>]*)>\s*Email\s*<input\b/i', $source) === 1,
    'locale placeholder is not embedded inside an input value attribute' => preg_match('/value\s*\n\s*\{\$localeFieldHtml\}/i', $source) !== 1,
    'no embedded PHP template tag after opening tag' => !str_contains($withoutOpeningTag, '<?=') && !str_contains($withoutOpeningTag, '<?php'),
    'locale select is rendered by helper method' => str_contains($source, 'name="locale"') && str_contains($source, 'admin-user-locale'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
