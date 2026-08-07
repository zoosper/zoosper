<?php

declare(strict_types=1);

use Zoosper\Core\Editor\ContentEditorInterface;
use Zoosper\Admin\Editor\ContentEditorRegistry;
use Zoosper\Admin\Editor\TextareaContentEditor;

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
