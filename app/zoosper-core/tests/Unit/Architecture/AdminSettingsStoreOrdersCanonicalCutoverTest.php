<?php

declare(strict_types=1);

it('wires Settings forms scope redirects and template navigation through canonical URLs', function (): void {
    $root = dirname(__DIR__, 5);
    $urls = (string) file_get_contents($root . '/app/zoosper-settings/src/Admin/SettingsAdminUrls.php');
    $view = (string) file_get_contents($root . '/app/zoosper-settings/resources/views/admin/settings/index.php');
    $wiring = (string) file_get_contents($root . '/app/zoosper-settings/config/controllers.php');

    expect($urls)->toContain('private ?AdminUrlGenerator $adminUrls = null')
        ->toContain("\$this->url('settings', ['scope' => \$type, 'scope_key' => \$key])")
        ->and($view)->toContain('action="<?= $e($saveUrl) ?>"')
        ->toContain('formaction="<?= $e($clearUrl) ?>"')
        ->not->toContain('action="/admin/settings')
        ->and($wiring)->toContain('$services->get(AdminUrlGenerator::class)')
        ->toContain('new SettingsAdminUrls(');
});

it('wires Store Orders workspace export table mutation and login URLs through the shared generator', function (): void {
    $root = dirname(__DIR__, 5);
    $workspace = (string) file_get_contents($root . '/packages/zoosper-store-orders/src/Admin/StoreOrderGridWorkspace.php');
    $controller = (string) file_get_contents($root . '/packages/zoosper-store-orders/src/Admin/StoreOrderAdminController.php');
    $services = (string) file_get_contents($root . '/packages/zoosper-store-orders/config/services.php');

    expect($workspace)->toContain("\$this->adminUrls?->url('store-orders') ?? self::ACTION")
        ->toContain("\$this->adminUrls?->url('store-orders/export') ?? self::ACTION . '/export'")
        ->and($controller)->toContain("\$this->workspace->action()")
        ->toContain("\$this->adminUrls?->url('login') ?? '/admin/login'")
        ->and($services)->toContain('adminUrls: $services->get(AdminUrlGenerator::class)');
});
