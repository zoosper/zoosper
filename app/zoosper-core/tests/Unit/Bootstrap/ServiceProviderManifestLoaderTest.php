<?php

declare(strict_types=1);

use Zoosper\Core\Bootstrap\ServiceProviderManifestLoader;

test('loads existing providers and rejects unknown provider classes', function (): void {
    $manifest = tempnam(__DIR__, 'providers-');
    if ($manifest === false) {
        throw new RuntimeException('Unable to create manifest fixture.');
    }

    try {
        file_put_contents($manifest, '<?php return ' . var_export(['providers' => [ManifestLoaderTestProvider::class, ManifestLoaderTestProvider::class, 42]], true) . ';');
        $container = new stdClass();
        $loader = new ServiceProviderManifestLoader(__DIR__);

        expect($loader->load($container, $manifest))->toBe(1)
            ->and($container->registered)->toBeTrue();

        file_put_contents($manifest, '<?php return ["MissingManifestLoaderTestProvider"];');
        expect(fn () => $loader->load($container, $manifest))
            ->toThrow(RuntimeException::class, 'Configured service provider does not exist: MissingManifestLoaderTestProvider');
    } finally {
        unlink($manifest);
    }
});

final class ManifestLoaderTestProvider
{
    public function register(object $container): void
    {
        $container->registered = true;
    }
}