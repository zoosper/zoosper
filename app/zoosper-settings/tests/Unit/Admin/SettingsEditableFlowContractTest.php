<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/Support/settings-presentation-bundle.php';

it('declares a protected save route and CSRF-protected scoped section form', function (): void {
    $root = dirname(__DIR__, 5);
    $routes = require $root . '/app/zoosper-settings/config/admin_routes.php';
    $view = settingsPresentationBundle($root);

    expect($routes)->toContain([
        'method' => 'POST',
        'path' => '/admin/settings/save',
        'controller' => \Zoosper\Settings\Controller\SettingsCatalogueController::class,
        'action' => 'save',
        'permission' => 'settings.manage',
    ])->and($view)->toContain('method="post"')
        ->toContain('action="<?= $e($saveUrl) ?>"')
        ->toContain('name="_csrf_token"')
        ->toContain('name="section"')
        ->toContain('name="scope"')
        ->toContain('name="scope_key"')
        ->not->toContain('type="password"');
});

it('keeps section and scope authority on the server', function (): void {
    $root = dirname(__DIR__, 5);
    $source = file_get_contents($root . '/app/zoosper-settings/src/Admin/SettingsMutationCoordinator.php');

    expect($source)->toContain('private function findSection')
        ->toContain('$this->scopeSelection->select(')
        ->toContain('$this->writer->write($section, $scopeType, $resolvedKey, $settings)')
        ->toContain('$effective->source === \'project\'')
        ->not->toContain('$_POST');
});
