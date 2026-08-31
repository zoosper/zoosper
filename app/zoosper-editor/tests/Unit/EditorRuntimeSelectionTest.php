<?php

declare(strict_types=1);

namespace Zoosper\Editor\Tests\Unit;

use Zoosper\Core\Editor\ContentEditorInterface;
use Zoosper\Editor\ContentEditorRegistry;
use Zoosper\Editor\TextareaContentEditor;

it('preserves the public variadic registry and late extension replacement', function (): void {
    $first = new TextareaContentEditor();
    $replacement = new class implements ContentEditorInterface {
        public function code(): string { return 'textarea'; }
        public function render(string $fieldName, string $value, array $context = []): string { return 'replacement'; }
    };
    $registry = new ContentEditorRegistry($first);
    $registry->register($replacement);

    expect($registry->get('textarea'))->toBe($replacement)
        ->and($registry->preferred('missing', 'textarea'))->toBe($replacement);
});










