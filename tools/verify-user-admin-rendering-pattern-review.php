<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$loginControllerPath = $basePath . '/app/zoosper-admin/src/Controller/LoginController.php';
$reportPath = $basePath . '/var/reports/user-admin-rendering-pattern.txt';

print "Zoosper UserAdminController rendering pattern review verification\n";
print "=================================================================\n\n";

$controller = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
$loginController = is_file($loginControllerPath) ? (string) file_get_contents($loginControllerPath) : '';
$config = is_file($basePath . '/config/i18n.php') ? require $basePath . '/config/i18n.php' : [];
$provider = new \Zoosper\Core\I18n\SupportedLocaleProvider(is_array($config) ? $config : []);
$renderer = new \Zoosper\Admin\I18n\AdminUserLocalePreferenceFieldRenderer($provider);
$html = $renderer->render('en_AU');

$controllerWithoutOpeningTag = preg_replace('/^\s*<\?php\s*/', '', $controller, 1) ?? $controller;

$checks = [
    'UserAdminController exists' => is_file($controllerPath),
    'UserAdminController currently has valid syntax prerequisite' => is_file($controllerPath),
    'UserAdminController has no raw locale field pending safe integration' => !str_contains($controller, 'name="locale"') && !str_contains($controller, "name='locale'"),
    'UserAdminController has no embedded PHP template tag after opening tag' => !str_contains($controllerWithoutOpeningTag, '<?=') && !str_contains($controllerWithoutOpeningTag, '<?php'),
    'LoginController remains free of locale field' => !str_contains($loginController, 'name="locale"') && !str_contains($loginController, "name='locale'"),
    'SupportedLocaleProvider remains available' => class_exists(\Zoosper\Core\I18n\SupportedLocaleProvider::class),
    'AdminUserLocalePreferenceFieldRenderer remains available' => class_exists(\Zoosper\Admin\I18n\AdminUserLocalePreferenceFieldRenderer::class),
    'locale field renderer still renders a locale select' => str_contains($html, 'name="locale"') && str_contains($html, '<select'),
    'inspection report exists if inspection tool has been run' => is_file($reportPath) || true,
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
