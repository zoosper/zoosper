<?php

declare(strict_types=1);

/** Phase 9F5E-L protects the shared route/menu transformation boundary. */
it('keeps admin declaration expansion central generic and immutable', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-core/src/Url/AdminPathCollectionTransformer.php');

    expect($source)->toContain('final readonly class AdminPathCollectionTransformer')
        ->toContain('public function routes(array $routes): array')
        ->toContain('public function menu(array $items): array')
        ->toContain('expandCanonicalPath')
        ->not->toContain('require ')
        ->not->toContain('ModuleRegistry')
        ->not->toContain('ServiceContainer')
        ->not->toContain('$_SERVER')
        ->not->toContain('$_ENV');
});
