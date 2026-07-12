<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$loginController = $basePath . '/app/zoosper-admin/src/Controller/LoginController.php';
$userAdminController = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';

print "Zoosper admin user locale preference UI verification\n";
print "====================================================\n\n";

$config = is_file($basePath . '/config/i18n.php') ? require $basePath . '/config/i18n.php' : [];
$provider = new \Zoosper\Core\I18n\SupportedLocaleProvider(is_array($config) ? $config : []);
$renderer = new \Zoosper\Admin\I18n\AdminUserLocalePreferenceFieldRenderer($provider);
$html = $renderer->render('en_AU');
$loginSource = is_file($loginController) ? (string) file_get_contents($loginController) : '';
$userAdminSource = is_file($userAdminController) ? (string) file_get_contents($userAdminController) : '';

$checks = [
    'SupportedLocaleProvider exists' => class_exists(\Zoosper\Core\I18n\SupportedLocaleProvider::class),
    'AdminUserLocalePreferenceFieldRenderer exists' => class_exists(\Zoosper\Admin\I18n\AdminUserLocalePreferenceFieldRenderer::class),
    'renderer outputs locale select' => str_contains($html, 'name="locale"') && str_contains($html, '<select'),
    'renderer outputs configured locale option' => str_contains($html, 'en_AU') && str_contains($html, 'English (Australia)'),
    'renderer selects current locale' => str_contains($html, 'value="en_AU" selected'),
    'renderer includes blank configured-locale fallback option' => str_contains($html, 'Use configured admin locale'),
    'SupportedLocaleProvider rejects unsafe locale' => !$provider->isSupportedAdminLocale('../bad'),
    'LoginController has no raw locale select inserted' => !str_contains($loginSource, 'name="locale"') && !str_contains($loginSource, "name='locale'"),
    'UserAdminController is present for future locale UI persistence work' => is_file($userAdminController) && str_contains($userAdminSource, 'UserAdminController'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
