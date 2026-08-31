<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('honours path visibility and exposes the protected clear action', function (): void {
    $root = dirname(__DIR__, 5);
    $routes = require $root . '/app/zoosper-settings/config/admin_routes.php';
    $view = settingsPresentationBundle($root);

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
    $view = settingsPresentationBundle($root);
    $hidden = strpos($view, 'type="hidden" name="<?= $e($inputName) ?>" value="0"');
    $checkbox = strpos($view, 'type="checkbox" name="<?= $e($inputName) ?>" value="1"');

    expect($hidden)->not->toBeFalse()
        ->and($checkbox)->not->toBeFalse()
        ->and($hidden)->toBeLessThan($checkbox);
});










