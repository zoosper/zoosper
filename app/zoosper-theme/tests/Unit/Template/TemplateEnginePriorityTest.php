<?php

declare(strict_types=1);

use Zoosper\Theme\Template\Engine\PhpTemplateEngine;
use Zoosper\Theme\Template\Engine\TemplateEngineRegistry;

it('orders configured engine extensions first without removing pluggable engines', function (): void {
    $custom = new class implements \Zoosper\Theme\Template\Engine\TemplateEngineInterface {
        public function extensions(): array { return ['latte']; }
        public function renderFile(string $path, array $data): string { return ''; }
    };
    $registry = (new TemplateEngineRegistry($custom, new PhpTemplateEngine()))->prioritise(['php']);
    expect($registry->extensions())->toBe(['php', 'latte'])
        ->and($registry->forPath('view.latte'))->toBe($custom);
});










