<?php

declare(strict_types=1);

it('registers Settings composition collaborators as module services', function (): void {
    $root = dirname(__DIR__, 5);
    $services = (string) file_get_contents($root . '/app/zoosper-settings/config/services.php');

    expect($services)->toContain('SettingsPresentationBuilder::class =>')
        ->toContain('SettingsScopeSelection::class =>')
        ->toContain('SettingsAdminUrls::class =>')
        ->toContain('$services->get(SiteRepository::class)')
        ->toContain('$services->get(AdminUrlGenerator::class)');
});

it('resolves Settings composition collaborators instead of constructing them in controller wiring', function (): void {
    $root = dirname(__DIR__, 5);
    $controllers = (string) file_get_contents($root . '/app/zoosper-settings/config/controllers.php');

    expect($controllers)->toContain('$services->get(SettingsPresentationBuilder::class)')
        ->toContain('$services->get(SettingsScopeSelection::class)')
        ->toContain('$services->get(SettingsAdminUrls::class)')
        ->not->toContain('new SettingsPresentationBuilder()')
        ->not->toContain('new SettingsScopeSelection(')
        ->not->toContain('new SettingsAdminUrls(');
});
