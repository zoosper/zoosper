<?php

declare(strict_types=1);

it('owns namespaced frontend navigation and breadcrumb templates', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Frontend/MenuFrontendNavigationContributor.php');

    expect($source)
        ->toContain('FrontendNavigationContributorInterface')
        ->toContain('zoosper-menu::frontend/menu/navigation.latte')
        ->toContain('zoosper-menu::frontend/menu/breadcrumbs.latte');

    expect($root . '/resources/views/frontend/menu/navigation.latte')->toBeFile()
        ->and($root . '/resources/views/frontend/menu/navigation-children.latte')->toBeFile()
        ->and($root . '/resources/views/frontend/menu/breadcrumbs.latte')->toBeFile();
});

it('protects new-window links and emits accessible breadcrumb state', function (): void {
    $root = dirname(__DIR__, 3);
    $navigation = (string) file_get_contents($root . '/resources/views/frontend/menu/navigation.latte');
    $breadcrumbs = (string) file_get_contents($root . '/resources/views/frontend/menu/breadcrumbs.latte');

    expect($navigation)->toContain('noopener noreferrer')->toContain('site-menu__item')
        ->and($breadcrumbs)->toContain('aria-current="page"')->toContain('<ol');
});










