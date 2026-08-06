<?php

declare(strict_types=1);

it('derives Default editor configuration through the explicit-scope factory', function (): void {
    $root = dirname(__DIR__, 5);
    $services = file_get_contents($root . '/app/zoosper-admin/config/services.php');
    $factory = file_get_contents($root . '/app/zoosper-admin/src/Editor/Config/ContentEditorRuntimeConfigFactory.php');

    expect($services)->toContain('ContentEditorRuntimeConfigFactory::class')
        ->toContain('->get(ContentEditorRuntimeConfigFactory::class)')
        ->toContain('->forDefaultScope()')
        ->and($factory)->toContain('public function forScope(ScopeContext $scope): ContentEditorRuntimeConfig')
        ->toContain('public function forDefaultScope(): ContentEditorRuntimeConfig');
});

it('keeps Page coupled to the editor contract rather than concrete Admin editors', function (): void {
    $root = dirname(__DIR__, 5);
    $controller = file_get_contents($root . '/app/zoosper-page/src/Admin/Controller/PageAdminController.php');
    $factory = file_get_contents($root . '/app/zoosper-page/config/controllers.php');

    expect($controller)->toContain('?ContentEditorInterface')
        ->not->toContain('EditorJsContentEditor')
        ->not->toContain('TextareaContentEditor')
        ->and($factory)->toContain('ContentEditorInterface::class');
});

it('keeps Media optional at the editor adapter boundary', function (): void {
    $root = dirname(__DIR__, 5);
    $services = file_get_contents($root . '/app/zoosper-admin/config/services.php');
    $editor = file_get_contents($root . '/app/zoosper-admin/src/Editor/EditorJsContentEditor.php');

    expect($services)->toContain('$services->has(EditorJsImageToolConfig::class)')
        ->and($editor)->toContain('private ?EditorJsImageToolConfig $imageToolConfig = null')
        ->toContain('private ?CsrfTokenManager $csrf = null');
});
