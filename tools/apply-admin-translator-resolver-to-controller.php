<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controllerPath = $basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php';

print "Zoosper admin translator resolver controller wiring\n";
print "===================================================\n\n";

if (!is_file($controllerPath)) {
    fwrite(STDERR, "PageAdminController.php was not found at {$controllerPath}.\n");
    exit(2);
}

$source = (string) file_get_contents($controllerPath);
$original = $source;

if (!str_contains($source, 'use Zoosper\\Core\\I18n\\AdminTranslatorResolver;')) {
    if (str_contains($source, 'use Zoosper\\Core\\I18n\\TranslationResolver;')) {
        $source = str_replace(
            'use Zoosper\\Core\\I18n\\TranslationResolver;' . PHP_EOL,
            'use Zoosper\\Core\\I18n\\AdminTranslatorResolver;' . PHP_EOL
            . 'use Zoosper\\Core\\I18n\\TranslationResolver;' . PHP_EOL,
            $source,
        );
    } elseif (str_contains($source, 'use Zoosper\\Core\\I18n\\TranslatorInterface;')) {
        $source = str_replace(
            'use Zoosper\\Core\\I18n\\TranslatorInterface;' . PHP_EOL,
            'use Zoosper\\Core\\I18n\\AdminTranslatorResolver;' . PHP_EOL
            . 'use Zoosper\\Core\\I18n\\TranslatorInterface;' . PHP_EOL,
            $source,
        );
    } else {
        fwrite(STDERR, "Unable to locate an I18n import insertion point.\n");
        exit(2);
    }
}

$replacement = <<<'PHP_CODE'
    private function defaultTranslator(): TranslatorInterface
    {
        $i18nConfig = $this->config?->array('i18n') ?? [];

        return (new AdminTranslatorResolver($this->projectRootPath(), $i18nConfig))->resolve();
    }

PHP_CODE;

$pattern = '/    private function defaultTranslator\(\): TranslatorInterface\n    \{.*?\n    \}\n\n/s';
$count = 0;
$source = preg_replace($pattern, $replacement, $source, 1, $count);

if (!is_string($source) || $count !== 1) {
    fwrite(STDERR, "Unable to replace defaultTranslator() safely.\n");
    exit(2);
}

if ($source === $original) {
    print "No changes required. PageAdminController is already wired.\n";
    exit(0);
}

$backupPath = $controllerPath . '.phase-0.95.bak';
if (!is_file($backupPath)) {
    file_put_contents($backupPath, $original);
}

file_put_contents($controllerPath, $source);
print "Updated: app/zoosper-admin/src/Controller/PageAdminController.php\n";
print "Backup: app/zoosper-admin/src/Controller/PageAdminController.php.phase-0.95.bak\n";
