<?php

declare(strict_types=1);

it('keeps presentation calculations out of the Settings template', function (): void {
    $root = dirname(__DIR__, 5);
    $view = (string) file_get_contents($root . '/app/zoosper-settings/resources/views/admin/settings/index.php');
    $builder = (string) file_get_contents($root . '/app/zoosper-settings/src/Admin/SettingsPresentationBuilder.php');

    expect($view)->not->toContain('$categoryOrder =')
        ->not->toContain('array_filter($section->settings')
        ->not->toContain('json_decode($effective->value')
        ->not->toContain('$inputType=match(')
        ->not->toContain('array_reduce($siteOptions')
        ->and($builder)->toContain('CATEGORY_ORDER')
        ->toContain("'fieldPresentation'")
        ->toContain("'scopeOptionsJson'");
});
