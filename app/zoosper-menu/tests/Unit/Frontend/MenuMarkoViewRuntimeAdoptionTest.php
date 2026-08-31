<?php

declare(strict_types=1);

use Marko\View\ViewInterface;
use Zoosper\Menu\Frontend\MenuFrontendNavigationContributor;

it('uses Marko ViewInterface for real frontend menu and breadcrumb rendering', function (): void {
    $constructor = (new ReflectionClass(MenuFrontendNavigationContributor::class))->getConstructor();
    expect($constructor)->not->toBeNull();

    $views = null;
    foreach ($constructor->getParameters() as $parameter) {
        if ($parameter->getName() === 'views') {
            $views = $parameter;
            break;
        }
    }

    expect($views)->not->toBeNull()
        ->and((string) $views->getType())->toBe(ViewInterface::class);
});

it('removes the concrete theme renderer from the menu runtime boundary', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Frontend/MenuFrontendNavigationContributor.php');
    $services = (string) file_get_contents($root . '/config/services.php');

    expect($source)
        ->toContain('$this->views->renderToString(')
        ->not->toContain('TemplateRenderer')
        ->not->toContain("'frontend.menu'")
        ->not->toContain("'frontend.breadcrumbs'")
        ->and($services)
        ->toContain('$s->get(ViewInterface::class)')
        ->not->toContain('theme.frontend_template_renderer');
});

it('keeps module-qualified templates at the Marko view boundary', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Frontend/MenuFrontendNavigationContributor.php');

    expect($source)
        ->toContain('zoosper-menu::frontend/menu/navigation.latte')
        ->toContain('zoosper-menu::frontend/menu/breadcrumbs.latte');
});










