<?php

declare(strict_types=1);

use Marko\View\ViewInterface;
use Zoosper\Theme\Template\MarkoViewAdapter;

it('provides an additive Zoosper implementation of the Marko view contract', function (): void {
    expect(is_subclass_of(MarkoViewAdapter::class, ViewInterface::class))->toBeTrue();

    $reflection = new ReflectionClass(MarkoViewAdapter::class);
    expect($reflection->hasMethod('render'))->toBeTrue()
        ->and($reflection->hasMethod('renderToString'))->toBeTrue();
});

it('registers the adapter without replacing the existing frontend renderer', function (): void {
    $root = dirname(__DIR__, 5);
    $services = (string) file_get_contents($root . '/app/zoosper-theme/config/services.php');
    $adapter = (string) file_get_contents($root . '/app/zoosper-theme/src/Template/MarkoViewAdapter.php');

    expect($services)
        ->toContain('ViewInterface::class')
        ->toContain("\$services->get('theme.frontend_template_renderer')")
        ->and($adapter)
        ->toContain('module::path naming convention')
        ->toContain('$this->templates->render(');
});
