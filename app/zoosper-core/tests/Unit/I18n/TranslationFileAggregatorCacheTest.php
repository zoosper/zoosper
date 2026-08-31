<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\I18n;

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










