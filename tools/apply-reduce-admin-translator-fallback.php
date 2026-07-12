<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php';

print "Zoosper reduce admin translator fallback\n";
print "=======================================\n\n";

if (!is_file($controllerPath)) {
    fwrite(STDERR, "Missing PageAdminController: {$controllerPath}\n");
    exit(2);
}

$source = file_get_contents($controllerPath);
if ($source === false) {
    fwrite(STDERR, "Unable to read PageAdminController.\n");
    exit(2);
}

$original = $source;
$source = str_replace("use Zoosper\\Core\\I18n\\AdminTranslatorResolver;\n", '', $source);
$source = str_replace("use Zoosper\\Core\\I18n\\TranslationResolver;\n", '', $source);

if (!str_contains($source, 'use Zoosper\\Core\\I18n\\IdentityTranslator;')) {
    $source = str_replace(
        "use Zoosper\\Core\\I18n\\TranslatorInterface;\n",
        "use Zoosper\\Core\\I18n\\IdentityTranslator;\nuse Zoosper\\Core\\I18n\\TranslatorInterface;\n",
        $source
    );
}

$source = preg_replace(
    '/\n\s*private function defaultTranslator\(\): TranslatorInterface\s*\{\s*\$i18nConfig = \$this->config\?->array\(\'i18n\'\) \?\? \[\];\s*return \(new AdminTranslatorResolver\(\$this->projectRootPath\(\), \$i18nConfig\)\)->resolve\(\);\s*\}/s',
    "\n    private function defaultTranslator(): TranslatorInterface\n    {\n        return new IdentityTranslator();\n    }",
    $source
);

if ($source === $original) {
    print "PageAdminController already appears to use reduced translator fallback.\n";
    exit(0);
}

$backupPath = $controllerPath . '.phase-1.01.bak';
if (!is_file($backupPath)) {
    copy($controllerPath, $backupPath);
    print "Backup: app/zoosper-admin/src/Controller/PageAdminController.php.phase-1.01.bak\n";
}

file_put_contents($controllerPath, $source);
print "Updated: app/zoosper-admin/src/Controller/PageAdminController.php\n";
print "Manual AdminTranslatorResolver fallback replaced with IdentityTranslator safety fallback.\n";
