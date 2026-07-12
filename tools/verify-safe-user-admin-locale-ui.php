<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$userAdminController = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';
$loginController = $basePath . '/app/zoosper-admin/src/Controller/LoginController.php';

print "Zoosper safe UserAdminController locale UI verification\n";
print "=======================================================\n\n";

$userAdminSource = is_file($userAdminController) ? (string) file_get_contents($userAdminController) : '';
$loginSource = is_file($loginController) ? (string) file_get_contents($loginController) : '';
$sourceWithoutOpeningTag = preg_replace('/^\s*<\?php\s*/', '', $userAdminSource, 1) ?? $userAdminSource;
$config = is_file($basePath . '/config/i18n.php') ? require $basePath . '/config/i18n.php' : [];
$provider = new \Zoosper\Core\I18n\SupportedLocaleProvider(is_array($config) ? $config : []);
$renderer = new \Zoosper\Admin\I18n\AdminUserLocalePreferenceFieldRenderer($provider);
$html = $renderer->render('en_AU');

$checks = [
    'UserAdminController exists' => is_file($userAdminController),
    'LoginController has no locale field' => !str_contains($loginSource, 'name="locale"') && !str_contains($loginSource, "name='locale'"),
    'UserAdminController uses localeFieldHtml placeholder' => str_contains($userAdminSource, '{$localeFieldHtml}'),
    'UserAdminController defines renderAdminLocaleField method' => str_contains($userAdminSource, 'function renderAdminLocaleField('),
    'UserAdminController render method contains locale select' => str_contains($userAdminSource, 'name="locale"') && str_contains($userAdminSource, 'admin-user-locale'),
    'UserAdminController has no embedded PHP template tag after opening tag' => !str_contains($sourceWithoutOpeningTag, '<?=') && !str_contains($sourceWithoutOpeningTag, '<?php'),
    'SupportedLocaleProvider exists' => class_exists(\Zoosper\Core\I18n\SupportedLocaleProvider::class),
    'AdminUserLocalePreferenceFieldRenderer exists' => class_exists(\Zoosper\Admin\I18n\AdminUserLocalePreferenceFieldRenderer::class),
    'renderer still outputs locale select safely' => str_contains($html, 'name="locale"') && str_contains($html, '<select'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
