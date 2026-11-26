<?php

declare(strict_types=1);

use Zoosper\Errors\ZoosperException;
use Zoosper\Theme\Template\Engine\LatteTemplateEngine;

it('renders escaped content using the configured Latte cache directory', function (): void {
    $root = dirname(__DIR__, 5);
    $cache = $root . '/var/latte-test-' . bin2hex(random_bytes(8));

    try {
        $engine = new LatteTemplateEngine($cache);
        $output = $engine->renderFile($root . '/themes/default/templates/examples/hello.latte', [
            'title' => '<script>alert(1)</script>',
            'message' => 'Hello & welcome',
        ]);

        expect($engine->extensions())->toBe(['latte'])
            ->and($output)->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')
            ->toContain('Hello &amp; welcome')
            ->and(glob($cache . '/*'))->not->toBeEmpty();

        expect(fn () => $engine->renderFile($root . '/missing-template.latte', []))
            ->toThrow(ZoosperException::class, 'Latte template rendering failed');
    } finally {
        foreach (glob($cache . '/*') ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($cache)) {
            rmdir($cache);
        }
    }
});