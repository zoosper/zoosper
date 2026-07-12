<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$schemaPath = $basePath . '/database/schema/admin_user_locale.php';

print "Zoosper admin user locale preference verification\n";
print "=================================================\n\n";

$schema = is_file($schemaPath) ? require $schemaPath : [];
$configuredResolver = new \Zoosper\Core\I18n\ConfiguredLocaleResolver([
    'default_locale' => 'en_AU',
    'admin_locale' => 'en_AU',
    'fallback_locale' => 'en_AU',
]);
$resolver = new \Zoosper\Core\I18n\AdminUserLocaleResolver($configuredResolver);

$userWithLocale = new class {
    public ?string $locale = 'en_GB';
};
$userWithInvalidLocale = new class {
    public ?string $locale = '../bad';
};
$userWithNullLocale = new class {
    public ?string $locale = null;
};

$checks = [
    'schema file exists' => is_file($schemaPath),
    'schema targets admin_users table' => is_array($schema) && ($schema['table'] ?? null) === 'admin_users',
    'schema defines locale column' => is_array($schema['columns']['locale'] ?? null),
    'AdminUserLocaleResolver exists' => class_exists(\Zoosper\Core\I18n\AdminUserLocaleResolver::class),
    'valid locale format accepted' => $resolver->isValidLocale('en_AU') && $resolver->isValidLocale('en_GB'),
    'invalid locale format rejected' => !$resolver->isValidLocale('en-au') && !$resolver->isValidLocale('../bad'),
    'null user falls back to configured admin locale' => $resolver->resolveForAdminUser(null)->activeLocale === 'en_AU',
    'user locale overrides configured admin locale' => $resolver->resolveForAdminUser($userWithLocale)->activeLocale === 'en_GB',
    'invalid user locale falls back to configured admin locale' => $resolver->resolveForAdminUser($userWithInvalidLocale)->activeLocale === 'en_AU',
    'null user locale falls back to configured admin locale' => $resolver->resolveForAdminUser($userWithNullLocale)->activeLocale === 'en_AU',
    'resolver returns admin scope' => $resolver->resolveForAdminUser($userWithLocale)->scope === 'admin',
    'resolver preserves fallback locale' => $resolver->resolveForAdminUser($userWithLocale)->fallbackLocale === 'en_AU',
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
