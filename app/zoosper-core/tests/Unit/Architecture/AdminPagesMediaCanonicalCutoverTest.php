<?php

declare(strict_types=1);

it('closes the secondary Pages builder while retaining canonical compatibility constants', function (): void {
    $root = dirname(__DIR__, 5);
    $builder = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/PageGridPageBuilder.php');
    $contract = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/PageGridEndpointContract.php');

    expect($builder)->toContain("\$this->adminUrls?->url('pages') ?? PageGridWorkspace::ACTION")
        ->toContain("\$this->adminUrls?->url('pages/grid') ?? self::MUTATION_PATH")
        ->and($contract)->toContain("public const VIEW_PATH = '/admin/pages'")
        ->toContain("public const MUTATION_PATH = '/admin/pages/grid'");
});

it('wires Media library and Editor.js runtime URLs through the canonical generator', function (): void {
    $root = dirname(__DIR__, 5);
    $controller = (string) file_get_contents($root . '/packages/zoosper-media/src/Controller/MediaAdminController.php');
    $controllers = (string) file_get_contents($root . '/packages/zoosper-media/config/controllers.php');
    $services = (string) file_get_contents($root . '/packages/zoosper-media/config/services.php');
    $upload = (string) file_get_contents($root . '/packages/zoosper-media/resources/views/admin/media/upload.php');

    expect($controller)->toContain('private ?AdminUrlGenerator $adminUrls = null')
        ->toContain("\$this->adminUrl('media/upload')")
        ->toContain("\$this->adminUrl('media')")
        ->and($controllers)->toContain('adminUrls: $services->get(AdminUrlGenerator::class)')
        ->and($services)->toContain("->url('media/editorjs/upload')")
        ->and($upload)->not->toContain('href="/admin/media"');
});










