<?php

declare(strict_types=1);

it('honours path visibility and exposes the protected clear action', function (): void {
    $root = dirname(__DIR__, 5);
    $routes = require $root . '/app/zoosper-settings/config/admin_routes.php';
    $view = file_get_contents($root . '/app/zoosper-settings/resources/views/admin/settings/index.php');

    expect($routes)->toContain([
        'method' => 'POST',
        'path' => '/admin/settings/clear',
        'controller' => \Zoosper\Settings\Controller\SettingsCatalogueController::class,
        'action' => 'clear',
        'permission' => 'settings.manage',
    ])->and($view)->toContain('if($showPaths)')
        ->toContain('formaction="<?= $e($clearUrl) ?>"')
        ->toContain('Use inherited value');
});

it('renders the hidden boolean fallback before the checkbox value', function (): void {
    $root = dirname(__DIR__, 5);
    $view = file_get_contents($root . '/app/zoosper-settings/resources/views/admin/settings/index.php');
    $hidden = strpos($view, 'type="hidden" name="<?= $e($inputName) ?>" value="0"');
    $checkbox = strpos($view, 'type="checkbox" name="<?= $e($inputName) ?>" value="1"');

    expect($hidden)->not->toBeFalse()
        ->and($checkbox)->not->toBeFalse()
        ->and($hidden)->toBeLessThan($checkbox);
});
