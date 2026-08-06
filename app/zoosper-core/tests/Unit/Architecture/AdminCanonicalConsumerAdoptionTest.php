<?php

declare(strict_types=1);

it('uses the shared canonical generator for layout and two-factor runtime consumers', function (): void {
    $root = dirname(__DIR__, 5);
    $layout = (string) file_get_contents($root . '/app/zoosper-admin/src/Layout/AdminLayout.php');
    $adminServices = (string) file_get_contents($root . '/app/zoosper-admin/config/services.php');
    $twoFactorControllers = (string) file_get_contents($root . '/app/zoosper-two-factor/config/controllers.php');
    $twoFactorServices = (string) file_get_contents($root . '/app/zoosper-two-factor/config/services.php');

    expect($layout)->toContain('private ?AdminUrlGenerator $adminUrls = null')
        ->toContain('return $this->adminUrls->url($path)')
        ->and($adminServices)->toContain('$services->get(AdminUrlGenerator::class)')
        ->and($twoFactorControllers)->toContain('$services->get(AdminUrlGenerator::class)->basePath()')
        ->not->toContain("array('admin')")
        ->and($twoFactorServices)->toContain('$services->get(AdminUrlGenerator::class)->basePath()');
});

it('retains backwards-compatible layout construction fallback', function (): void {
    $root = dirname(__DIR__, 5);
    $layout = (string) file_get_contents($root . '/app/zoosper-admin/src/Layout/AdminLayout.php');

    expect($layout)->toContain("\$this->config?->array('admin') ?? []")
        ->toContain("\$adminConfig['base_path'] ?? '/admin'");
});
