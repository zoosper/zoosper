<?php

declare(strict_types=1);

it('keeps Mail dependent on Core rather than the Admin-oriented Settings package', function (): void {
    $root = dirname(__DIR__, 5);
    $composer = json_decode(file_get_contents($root . '/app/zoosper-mail/composer.json'), true, flags: JSON_THROW_ON_ERROR);
    $services = file_get_contents($root . '/app/zoosper-mail/config/services.php');
    $factory = file_get_contents($root . '/app/zoosper-mail/src/Config/SmtpConfigFactory.php');

    expect($composer['require'])->toHaveKey('zoosper/core')
        ->not->toHaveKey('zoosper/settings')
        ->and($services)->toContain('ScopeConfigRepository')
        ->toContain('SmtpConfigFactory::class')
        ->and($factory)->toContain('ScopeContext::default()')
        ->toContain('public function forDefaultScope(): SmtpConfig');
});

it('keeps the catalogue read-only until secret writes are explicitly supported', function (): void {
    $root = dirname(__DIR__, 5);
    $settings = file_get_contents($root . '/app/zoosper-mail/config/admin_settings.php');

    expect(substr_count($settings, "'read_only' => true"))->toBe(9)
        ->and($settings)->toContain("'secret' => true");
});










