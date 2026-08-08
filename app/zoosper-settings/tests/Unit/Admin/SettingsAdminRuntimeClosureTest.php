<?php

declare(strict_types=1);

it('keeps the Settings controller as a thin authenticated adapter', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-settings/src/Controller/SettingsCatalogueController.php');

    expect(substr_count($source, 'private '))->toBeLessThanOrEqual(4)
        ->and($source)->toContain('SettingsCatalogueResponder')
        ->toContain('SettingsMutationCoordinator')
        ->not->toContain('ModuleSettingsCatalogueLoader')
        ->not->toContain('ScopedSettingValueResolver')
        ->not->toContain('SectionSettingsWriter')
        ->not->toContain('ScopedSettingClearer')
        ->not->toContain('FlashMessageStoreInterface')
        ->not->toContain('AdminViewRendererInterface')
        ->not->toContain('ScopeType::');
});

it('wires Settings screen and mutation ownership through Settings collaborators', function (): void {
    $root = dirname(__DIR__, 5);
    $factory = (string) file_get_contents($root . '/app/zoosper-settings/config/controllers.php');
    $responder = (string) file_get_contents($root . '/app/zoosper-settings/src/Admin/SettingsCatalogueResponder.php');
    $mutations = (string) file_get_contents($root . '/app/zoosper-settings/src/Admin/SettingsMutationCoordinator.php');

    expect($factory)->toContain('new SettingsCatalogueResponder(')
        ->toContain('new SettingsMutationCoordinator(')
        ->toContain('$services->get(SettingsAdminUrls::class)')
        ->and($responder)->toContain("template: 'zoosper-settings::admin/settings/index'")
        ->toContain("'csrfToken' => \$this->csrf->token()")
        ->and($mutations)->toContain("->sectionSaved(")
        ->toContain("->overrideCleared(")
        ->toContain("->success('Settings saved.'")
        ->toContain("->success('Setting override cleared.'");
});
