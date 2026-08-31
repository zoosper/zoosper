<?php

declare(strict_types=1);

use Marko\View\ViewInterface;
use Zoosper\Page\Service\PageRenderer;

it('accepts Marko ViewInterface as an optional Page content renderer', function (): void {
    $constructor = (new ReflectionClass(PageRenderer::class))->getConstructor();
    expect($constructor)->not->toBeNull();

    $views = null;
    foreach ($constructor->getParameters() as $parameter) {
        if ($parameter->getName() === 'views') {
            $views = $parameter;
            break;
        }
    }

    expect($views)->not->toBeNull()
        ->and((string) $views->getType())->toBe('?' . ViewInterface::class)
        ->and($views->isDefaultValueAvailable())->toBeTrue()
        ->and($views->getDefaultValue())->toBeNull();
});

it('renders the module page view through Marko while retaining Zoosper layout ownership', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Service/PageRenderer.php');
    $services = (string) file_get_contents($root . '/config/services.php');

    expect($source)
        ->toContain("\$this->views->renderToString('zoosper-page::page/view'")
        ->toContain("\$templates->renderLayout('layout'")
        ->toContain('marko/layout release')
        ->and($services)
        ->toContain('$services->has(ViewInterface::class)')
        ->toContain('$services->get(ViewInterface::class)');
});

it('keeps the pre-existing TemplateRenderer fallback for isolated Page tests and previews', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Service/PageRenderer.php');

    expect($source)
        ->toContain(': $templates->render(')
        ->toContain("'zoosper-page::page/view'");
});










