<?php

declare(strict_types=1);

use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Seo\Contract\SeoMetadataContributorInterface;
use Zoosper\Seo\Contract\SitemapContributorInterface;
use Zoosper\Seo\Contribution\SeoContributorRegistry;
use Zoosper\Seo\Metadata\SeoMetadata;
use Zoosper\Site\Model\Site;

function withSeoRegistryFixture(array $configs, callable $test): void
{
    $base = __DIR__ . '/seo-registry-' . bin2hex(random_bytes(8));
    $paths = [];
    try {
        foreach ($configs as $name => $config) {
            $module = $base . '/app/' . $name;
            mkdir($module . '/config', 0775, true);
            $paths[] = $module;
            file_put_contents($module . '/module.php', "<?php\ndeclare(strict_types=1);\nreturn " . var_export(['name' => $name, 'enabled' => true], true) . ";\n");
            if ($config !== null) {
                file_put_contents($module . '/config/seo.php', "<?php\ndeclare(strict_types=1);\nreturn " . var_export($config, true) . ";\n");
            }
        }
        $test(new ModuleRegistry($base));
    } finally {
        foreach ($paths as $module) {
            if (is_file($module . '/config/seo.php')) {
                unlink($module . '/config/seo.php');
            }
            unlink($module . '/module.php');
            rmdir($module . '/config');
            rmdir($module);
        }
        rmdir($base . '/app');
        rmdir($base);
    }
}

it('collects each group in module and declaration order, retaining duplicates and skipping invalid ids', function (): void {
    $metadata = new class implements SeoMetadataContributorInterface {
        public function contribute(object $resource, Site $site, ?Request $request = null): ?SeoMetadata { return null; }
    };
    $otherMetadata = new class implements SeoMetadataContributorInterface {
        public function contribute(object $resource, Site $site, ?Request $request = null): ?SeoMetadata { return null; }
    };
    $sitemap = new class implements SitemapContributorInterface {
        public function entries(Site $site): iterable { return []; }
    };
    $otherSitemap = new class implements SitemapContributorInterface {
        public function entries(Site $site): iterable { return []; }
    };
    $services = new ServiceContainer();
    $services->set('metadata', $metadata);
    $services->set('other-metadata', $otherMetadata);
    $services->set('sitemap', $sitemap);
    $services->set('other-sitemap', $otherSitemap);

    withSeoRegistryFixture([
        'a' => ['metadata' => ['', null, 123, 'metadata'], 'sitemap' => ['sitemap']],
        'b' => ['metadata' => ['other-metadata', 'metadata'], 'sitemap' => ['other-sitemap', 'sitemap']],
        'c' => null,
        'd' => [],
    ], function (ModuleRegistry $modules) use ($services, $metadata, $otherMetadata, $sitemap, $otherSitemap): void {
        $registry = new SeoContributorRegistry($modules, $services);
        expect($registry->metadata())->toBe([$metadata, $otherMetadata, $metadata])
            ->and($registry->sitemap())->toBe([$sitemap, $otherSitemap, $sitemap]);
    });
});

it('rejects a non-array contributor configuration', function (): void {
    withSeoRegistryFixture(['bad' => 'invalid'], function (ModuleRegistry $modules): void {
        $registry = new SeoContributorRegistry($modules, new ServiceContainer());
        expect(fn () => $registry->metadata())->toThrow(RuntimeException::class, 'SEO contributor config must return an array: ' . $modules->enabledModules()[0]->configPath('seo.php'));
    });
});

it('rejects services that do not implement the requested contract', function (string $group, string $contract): void {
    $services = new ServiceContainer();
    $services->set('wrong', new stdClass());
    withSeoRegistryFixture(['bad' => [$group => ['wrong']]], function (ModuleRegistry $modules) use ($services, $group, $contract): void {
        $registry = new SeoContributorRegistry($modules, $services);
        expect(fn () => $registry->$group())->toThrow(RuntimeException::class, sprintf('SEO contributor wrong must implement %s.', $contract));
    });
})->with([
    ['metadata', SeoMetadataContributorInterface::class],
    ['sitemap', SitemapContributorInterface::class],
]);