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
