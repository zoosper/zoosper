<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$providerPath = $basePath . '/app/zoosper-core/src/I18n/I18nServiceProvider.php';

print "Zoosper supported locale provider registration apply\n";
print "====================================================\n\n";

if (!is_file($providerPath)) {
    fwrite(STDERR, "Missing I18nServiceProvider.\n");
    exit(2);
}

$source = (string) file_get_contents($providerPath);
if (str_contains($source, 'SupportedLocaleProvider::class')) {
    print "- SupportedLocaleProvider already registered\n";
    print "Result: OK\n";
    exit(0);
}

$needle = '$this->registerService($container, AdminUserLocaleResolver::class, fn (): AdminUserLocaleResolver => new AdminUserLocaleResolver(new ConfiguredLocaleResolver($this->i18nConfig)));';
$insert = $needle . PHP_EOL . '        $this->registerService($container, SupportedLocaleProvider::class, fn (): SupportedLocaleProvider => new SupportedLocaleProvider($this->i18nConfig));';
if (!str_contains($source, $needle)) {
    fwrite(STDERR, "Unable to find AdminUserLocaleResolver registration insertion point.\n");
    exit(2);
}

$backupPath = $providerPath . '.phase-1.05.bak';
if (!is_file($backupPath)) {
    copy($providerPath, $backupPath);
    print "- backup created: app/zoosper-core/src/I18n/I18nServiceProvider.php.phase-1.05.bak\n";
}

file_put_contents($providerPath, str_replace($needle, $insert, $source));
print "- registered SupportedLocaleProvider\n";
print "Result: OK\n";
