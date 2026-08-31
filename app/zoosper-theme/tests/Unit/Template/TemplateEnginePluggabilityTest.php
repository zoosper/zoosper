<?php

declare(strict_types=1);

namespace Zoosper\Theme\Tests\Unit\Template;

use Zoosper\Theme\Template\Engine\TemplateEngineInterface;
use Zoosper\Theme\Template\Engine\TemplateEngineRegistry;

final class TestMarkdownTemplateEngine implements TemplateEngineInterface
{
    /** @return list<string> */
    public function extensions(): array
    {
        return ['.MDVIEW', 'md'];
    }

    /** @param array<string, mixed> $data */
    public function renderFile(string $path, array $data): string
    {
        return basename($path) . ':' . (string) ($data['title'] ?? '');
    }
}

test('a third-party template engine plugs in through the public contract', function (): void {
    $engine = new TestMarkdownTemplateEngine();
    $registry = new TemplateEngineRegistry($engine);
    $extensions = $registry->extensions();

    // Registry order is intentionally deterministic and alphabetic. The
    // contract is membership and lookup, not declaration order from an engine.
    expect($extensions)->toBe(['md', 'mdview']);
    expect($extensions)->toContain('md');
    expect($extensions)->toContain('mdview');
    expect($registry->forPath('/tmp/example.mdview'))->toBe($engine);
    expect($registry->forPath('/tmp/example.MD'))->toBe($engine);
    expect($registry->forPath('/tmp/example.mdview')->renderFile(
        '/tmp/example.mdview',
        ['title' => 'API first'],
    ))->toBe('example.mdview:API first');
});

test('an application module can override an extension without changing the registry', function (): void {
    $default = new TestMarkdownTemplateEngine();
    $override = new class implements TemplateEngineInterface {
        /** @return list<string> */
        public function extensions(): array
        {
            return ['mdview'];
        }

        /** @param array<string, mixed> $data */
        public function renderFile(string $path, array $data): string
        {
            return 'override:' . (string) ($data['title'] ?? '');
        }
    };

    $registry = new TemplateEngineRegistry($default);
    $registry->register($override);

    expect($registry->forPath('page.mdview'))->toBe($override);
    expect($registry->forPath('page.md'))->toBe($default);
});










