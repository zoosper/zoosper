<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Architecture;

/** @return list<string> */
function retiredDuplicateFiles(): array
{
    return [
        'app/zoosper-two-factor/src/Service/TotpVerifier.php',
        'app/zoosper-two-factor/src/Service/TotpSecretGenerator.php',
        'app/zoosper-two-factor/src/Service/Base32.php',
        'app/zoosper-two-factor/src/Service/RecoveryCodeGenerator.php',
        'app/zoosper-two-factor/src/Service/RecoveryCodeHasher.php',
        'app/zoosper-two-factor/src/Model/AdminTwoFactorProfile.php',
        'app/zoosper-site/src/Service/SiteResolver.php',
        'app/zoosper-site/src/Context/SiteContext.php',
        'app/zoosper-core/src/Translation/Translator.php',
        'app/zoosper-core/src/Translation/ModuleTranslationLoader.php',
        'app/zoosper-admin/src/Asset/AdminAssetRenderer.php',
        'app/zoosper-admin/src/Editor/AdminEditorConfig.php',
        'app/zoosper-admin/config/editor.php',
        'themes/admin/default/templates/components/grid/page-filters.php',
    ];
}

test('confirmed duplicate subsystem files remain retired', function (): void {
    $basePath = dirname(__DIR__, 5);

    foreach (retiredDuplicateFiles() as $relativePath) {
        expect(is_file($basePath . '/' . $relativePath))
            ->toBeFalse('Retired duplicate file returned: ' . $relativePath);
    }
});

test('canonical replacement implementations remain available', function (): void {
    expect(class_exists(\Zoosper\TwoFactor\Totp\TotpVerifier::class))->toBeTrue();
    expect(class_exists(\Zoosper\TwoFactor\Totp\TotpSecretGenerator::class))->toBeTrue();
    expect(class_exists(\Zoosper\TwoFactor\Totp\Base32::class))->toBeTrue();
    expect(class_exists(\Zoosper\TwoFactor\Recovery\RecoveryCodeGenerator::class))->toBeTrue();
    expect(class_exists(\Zoosper\Core\Site\SiteContext::class))->toBeTrue();
    expect(class_exists(\Zoosper\Core\I18n\ArrayTranslator::class))->toBeTrue();
    expect(class_exists(\Zoosper\Admin\Asset\AdminAssetTemplateRenderer::class))->toBeTrue();
    expect(class_exists(\Zoosper\Admin\Editor\ContentEditorRegistry::class))->toBeTrue();
});

test('site services no longer register the retired resolver', function (): void {
    $basePath = dirname(__DIR__, 5);
    $services = (string) file_get_contents($basePath . '/app/zoosper-site/config/services.php');

    expect($services)->not->toContain('Site\\Service\\SiteResolver');
    expect($services)->not->toContain('SiteResolver::class');
});
