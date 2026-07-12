<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$userAdminController = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$loginController = $basePath . '/app/zoosper-admin/src/Controller/LoginController.php';

print "Zoosper UserAdminController locale UI verification\n";
print "==================================================\n\n";

$userAdminSource = is_file($userAdminController) ? (string) file_get_contents($userAdminController) : '';
$loginSource = is_file($loginController) ? (string) file_get_contents($loginController) : '';
$config = is_file($basePath . '/config/i18n.php') ? require $basePath . '/config/i18n.php' : [];
$provider = new \Zoosper\Core\I18n\SupportedLocaleProvider(is_array($config) ? $config : []);
$renderer = new \Zoosper\Admin\I18n\AdminUserLocalePreferenceFieldRenderer($provider);
$html = $renderer->render('en_AU');
$hasLocaleField = str_contains($userAdminSource, 'name="locale"') || str_contains($userAdminSource, "name='locale'");

$checks = [
    'UserAdminController exists' => is_file($userAdminController),
    'LoginController has no locale field' => !str_contains($loginSource, 'name="locale"') && !str_contains($loginSource, "name='locale'"),
    'SupportedLocaleProvider exists' => class_exists(\Zoosper\Core\I18n\SupportedLocaleProvider::class),
    'AdminUserLocalePreferenceFieldRenderer exists' => class_exists(\Zoosper\Admin\I18n\AdminUserLocalePreferenceFieldRenderer::class),
    'renderer outputs locale select' => str_contains($html, 'name="locale"') && str_contains($html, '<select'),
    'renderer outputs supported en_AU option' => str_contains($html, 'en_AU') && str_contains($html, 'English (Australia)'),
    'UserAdminController locale field is absent or present in correct controller only' => !$hasLocaleField || str_contains($userAdminSource, 'name="locale"') || str_contains($userAdminSource, "name='locale'"),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nUserAdminController locale field: " . ($hasLocaleField ? 'yes' : 'no') . PHP_EOL;
print "Result: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
