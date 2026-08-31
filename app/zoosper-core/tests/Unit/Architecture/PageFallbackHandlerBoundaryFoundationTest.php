<?php

declare(strict_types=1);

use Zoosper\Core\Routing\FallbackHandlerInterface;
use Zoosper\Core\Routing\NullFallbackHandler;
use Zoosper\Page\Routing\PageFallbackHandler;
use Zoosper\Page\Routing\PageFallbackHandlerAdapter;

it('keeps the fallback handler contract core-owned', function (): void {
    $root = dirname(__DIR__, 5);
    $source = file_get_contents($root . '/app/zoosper-core/src/Routing/FallbackHandlerInterface.php');

    expect(interface_exists(FallbackHandlerInterface::class))->toBeTrue();
    expect($source)->not->toContain('Zoosper\Page\');
    expect($source)->toContain('supports(object $request): bool');
    expect($source)->toContain('handle(object $request): mixed');
});

it('provides a safe no-op fallback handler', function (): void {
    $handler = new NullFallbackHandler();
    $request = (object) ['path' => '/missing'];

    expect($handler)->toBeInstanceOf(FallbackHandlerInterface::class);
    expect($handler->supports($request))->toBeFalse();
    expect($handler->handle($request))->toBeNull();
});

it('keeps the legacy page fallback adapter compatible with the boundary', function (): void {
    $adapter = new PageFallbackHandlerAdapter();
    $request = (object) ['path' => '/missing'];

    expect($adapter)->toBeInstanceOf(FallbackHandlerInterface::class);
    expect($adapter->supports($request))->toBeFalse();
    expect($adapter->handle($request))->toBeNull();
});

it('adapts a page controller behind the fallback boundary', function (): void {
    $controller = new class {
        public function supports(object $request): bool
        {
            return $request->path === '/yes';
        }

        public function handle(object $request): string
        {
            return 'handled:' . $request->path;
        }
    };

    $handler = new PageFallbackHandler($controller);

    expect($handler->supports((object) ['path' => '/no']))->toBeFalse();
    expect($handler->handle((object) ['path' => '/no']))->toBeNull();
    expect($handler->supports((object) ['path' => '/yes']))->toBeTrue();
    expect($handler->handle((object) ['path' => '/yes']))->toBe('handled:/yes');
});










