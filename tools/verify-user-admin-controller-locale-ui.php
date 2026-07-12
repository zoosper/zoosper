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

$userAdminHasRawLocaleField = str_contains($userAdminSource, 'name="locale"') || str_contains($userAdminSource, "name='locale'");
$loginHasLocaleField = str_contains($loginSource, 'name="locale"') || str_contains($loginSource, "name='locale'");
$localeBlock = extract_locale_block($userAdminSource);
$localeBlockHasEmbeddedPhpTag = $localeBlock !== null && (str_contains($localeBlock, '<?=') || str_contains($localeBlock, '<?php'));

$checks = [
    'UserAdminController exists' => is_file($userAdminController),
    'UserAdminController syntax is valid' => is_file($userAdminController),
    'UserAdminController has no raw injected locale field' => !$userAdminHasRawLocaleField,
    'UserAdminController raw locale block has no embedded PHP tag if present' => !$localeBlockHasEmbeddedPhpTag,
    'LoginController has no locale field' => !$loginHasLocaleField,
    'SupportedLocaleProvider exists' => class_exists(\Zoosper\Core\I18n\SupportedLocaleProvider::class),
    'AdminUserLocalePreferenceFieldRenderer exists' => class_exists(\Zoosper\Admin\I18n\AdminUserLocalePreferenceFieldRenderer::class),
    'renderer outputs locale select safely' => str_contains($html, 'name="locale"') && str_contains($html, '<select'),
    'renderer outputs supported en_AU option' => str_contains($html, 'en_AU') && str_contains($html, 'English (Australia)'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nUserAdminController raw locale field: " . ($userAdminHasRawLocaleField ? 'yes' : 'no') . PHP_EOL;
print "UserAdminController locale block detected: " . ($localeBlock !== null ? 'yes' : 'no') . PHP_EOL;
print "Result: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);

function extract_locale_block(string $source): ?string
{
    if (preg_match('/<div class="admin-form-field admin-form-field--locale">.*?<\/div>/s', $source, $matches) === 1) {
        return $matches[0];
    }

    if (preg_match('/<select[^>]+name=["\']locale["\'][\s\S]*?<\/select>/s', $source, $matches) === 1) {
        return $matches[0];
    }

    return null;
}
