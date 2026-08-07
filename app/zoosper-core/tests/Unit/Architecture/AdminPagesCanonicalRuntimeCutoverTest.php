<?php

declare(strict_types=1);

it('wires the canonical generator across Pages CRUD Grid bulk and export runtime', function (): void {
    $root = dirname(__DIR__, 5);
    $wiring = (string) file_get_contents($root . '/app/zoosper-page/config/controllers.php');
    $controller = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/Controller/PageAdminController.php');
    $workspace = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/PageGridWorkspace.php');
    $definition = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/PageGridDefinition.php');

    expect($wiring)->toContain('$services->get(AdminUrlGenerator::class)')
        ->and($controller)->toContain('private ?AdminUrlGenerator')
        ->toContain("\$this->adminUrl('/pages/bulk-action')")
        ->and($workspace)->toContain("\$this->adminUrls?->url('pages') ?? '/admin/pages'")
        ->toContain("public const ACTION = '/admin/pages'")
        ->and($definition)->toContain("\$this->adminUrls?->url('pages/edit'")
        ->toContain("\$this->adminUrls?->url('pages/preview'");
});

it('removes literal admin page links from migrated Pages fallback templates', function (): void {
    $root = dirname(__DIR__, 5);
    foreach (['index.php', 'form.php'] as $file) {
        $source = (string) file_get_contents($root . '/app/zoosper-page/resources/views/admin/pages/' . $file);
        expect($source)->not->toContain('href="/admin/pages');
    }
    $assets = (string) file_get_contents($root . '/app/zoosper-page/config/admin_assets.php');
    expect($assets)->toContain('/assets/admin/');
});
