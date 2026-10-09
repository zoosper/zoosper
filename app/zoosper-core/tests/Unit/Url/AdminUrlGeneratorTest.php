<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Url\AdminUrlGenerator;

it('generates default admin URLs and encoded queries', function (): void {
    $urls = new AdminUrlGenerator(ConfigRepository::fromArray(['admin' => ['base_path' => '/admin']]));

    expect($urls->basePath())->toBe('/admin')
        ->and($urls->url('/pages/edit', ['id' => 42, 'notice' => 'saved value']))
        ->toBe('/admin/pages/edit?id=42&notice=saved%20value')
        ->and($urls->isAdminPath('/admin/pages?status=published'))->toBeTrue()
        ->and($urls->isAdminPath('/administrator'))->toBeFalse();
});

it('expands only the leading canonical admin segment', function (): void {
    $urls = new AdminUrlGenerator(ConfigRepository::fromArray(['admin' => ['base_path' => '/control-centre/']]));

    expect($urls->url('settings'))->toBe('/control-centre/settings')
        ->and($urls->expandCanonicalPath('/admin'))->toBe('/control-centre')
        ->and($urls->expandCanonicalPath('/admin/pages'))->toBe('/control-centre/pages')
        ->and($urls->expandCanonicalPath('/api/admin/pages'))->toBe('/api/admin/pages');
});

it('rejects root reserved and malformed admin paths', function (string $path): void {
    expect(fn () => new AdminUrlGenerator(
        ConfigRepository::fromArray(['admin' => ['base_path' => $path]]),
    ))->toThrow(\InvalidArgumentException::class);
})->with(['/', '/api', '/assets/admin', '/bad path', '/bad?path']);












it('validates Admin base path types while retaining defaults and custom strings', function (): void {
    foreach ([[], ['admin' => []], ['admin' => ['base_path' => null]]] as $items) {
        expect((new AdminUrlGenerator(ConfigRepository::fromArray($items)))->url('logout'))->toBe('/admin/logout');
    }
    $stringable = new class implements \Stringable {
        public function __toString(): string
        {
            return '/control';
        }
    };
    $resource = fopen('php://memory', 'r');
    try {
        foreach ([42, 1.5, true, false, [], new \stdClass(), $stringable, $resource] as $value) {
            expect(fn () => new AdminUrlGenerator(ConfigRepository::fromArray(['admin' => ['base_path' => $value]])))
                ->toThrow(\InvalidArgumentException::class, 'Configuration admin.base_path must be a string.');
        }
    } finally {
        if (is_resource($resource)) {
            fclose($resource);
        }
    }
});

it('preserves mixed configuration arrays and isolates layout fallback type validation', function (): void {
    $values = [0 => 42, 'flag' => true, 'nested' => ['x'], 'null' => null];
    expect(ConfigRepository::fromArray(['example' => $values])->array('example'))->toBe($values);
    // Exercise only the URL helper, without templates, navigation or application boot.
    $reflection = new \ReflectionClass(\Zoosper\Admin\Layout\AdminLayout::class);
    $method = $reflection->getMethod('adminUrl');
    foreach ([[null, '/admin/logout'], ['/control/', '/control/logout']] as [$value, $expected]) {
        $layout = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('config')->setValue($layout, ConfigRepository::fromArray(['admin' => ['base_path' => $value]]));
        $reflection->getProperty('adminUrls')->setValue($layout, null);
        expect($method->invoke($layout, '/logout'))->toBe($expected);
    }
    foreach ([42, true, false, [], new \stdClass()] as $value) {
        $layout = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('config')->setValue($layout, ConfigRepository::fromArray(['admin' => ['base_path' => $value]]));
        $reflection->getProperty('adminUrls')->setValue($layout, null);
        expect(fn () => $method->invoke($layout, '/logout'))
            ->toThrow(\InvalidArgumentException::class, 'Configuration admin.base_path must be a string.');
    }
});
