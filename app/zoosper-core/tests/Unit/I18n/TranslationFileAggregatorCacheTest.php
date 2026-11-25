<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\I18n;

use Zoosper\Core\I18n\AdminTranslatorResolver;
use Zoosper\Core\I18n\TranslationFileAggregator;

it('memoizes merged translation catalogues per locale and fallback', function (): void {
    $tempDir = sys_get_temp_dir() . '/zoosper_i18n_test_' . bin2hex(random_bytes(4));
    $i18nDir = $tempDir . '/app/test-module/i18n';
    mkdir($i18nDir, 0777, true);
    file_put_contents($i18nDir . '/fr_FR.php', "<?php\nreturn ['hello' => 'bonjour'];\n");

    try {
        $aggregator = new TranslationFileAggregator($tempDir);
        $first = $aggregator->catalogue('fr_FR', 'en_AU');
        $second = $aggregator->catalogue('fr_FR', 'en_AU');

        expect($first)->toBe($second)
            ->and($first->get('hello'))->toBe('bonjour')
            ->and($first->locale)->toBe('fr_FR');
    } finally {
        @unlink($i18nDir . '/fr_FR.php');
        @rmdir($i18nDir);
        @rmdir($tempDir . '/app/test-module');
        @rmdir($tempDir . '/app');
        @rmdir($tempDir);
    }
});

it('handles missing translation files and merges fallback and locale files', function (): void {
    $tempDir = sys_get_temp_dir() . '/zoosper_i18n_test_' . bin2hex(random_bytes(4));
    $i18nDir = $tempDir . '/config/i18n';
    mkdir($i18nDir, 0777, true);
    file_put_contents($i18nDir . '/en_AU.php', "<?php\nreturn ['shared' => 'fallback', 'only_fallback' => 'default'];\n");
    file_put_contents($i18nDir . '/fr_FR.php', "<?php\nreturn ['shared' => 'translated'];\n");

    try {
        $aggregator = new TranslationFileAggregator($tempDir);
        $catalogue = $aggregator->catalogue('fr_FR', 'en_AU');
        expect($catalogue->get('shared'))->toBe('translated')
            ->and($catalogue->get('only_fallback'))->toBe('default')
            ->and($aggregator->catalogue('missing', 'missing')->messages())->toBe([]);
    } finally {
        unlink($i18nDir . '/en_AU.php');
        unlink($i18nDir . '/fr_FR.php');
        rmdir($i18nDir);
        rmdir($tempDir . '/config');
        rmdir($tempDir);
    }
});

it('resolves admin translations using the configured locale and fallback', function (): void {
    $tempDir = sys_get_temp_dir() . '/zoosper_i18n_test_' . bin2hex(random_bytes(4));
    $i18nDir = $tempDir . '/config/i18n';
    mkdir($i18nDir, 0777, true);
    file_put_contents($i18nDir . '/en_AU.php', "<?php\nreturn ['fallback' => 'default'];\n");
    file_put_contents($i18nDir . '/fr_FR.php', "<?php\nreturn ['hello' => 'bonjour'];\n");

    try {
        $resolver = new AdminTranslatorResolver($tempDir, ['admin_locale' => ' fr_FR ', 'fallback_locale' => 'en_AU']);
        $translator = $resolver->resolve();
        expect($resolver->resolveLocale()->activeLocale)->toBe('fr_FR')
            ->and($translator->translate('hello'))->toBe('bonjour')
            ->and($translator->translate('fallback'))->toBe('default')
            ->and($translator->translate('missing'))->toBe('missing');
    } finally {
        unlink($i18nDir . '/en_AU.php');
        unlink($i18nDir . '/fr_FR.php');
        rmdir($i18nDir);
        rmdir($tempDir . '/config');
        rmdir($tempDir);
    }
});










