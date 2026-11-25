<?php

declare(strict_types=1);

namespace Zoosper\Editor\Tests\Unit;

use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Editor\ContentEditorInterface;
use Zoosper\Core\Editor\EditorImageToolConfigInterface;
use Zoosper\Editor\ContentEditorRegistry;
use Zoosper\Editor\EditorJsContentEditor;
use Zoosper\Editor\TextareaContentEditor;

it('selects Editor.js while retaining the submitted textarea fallback', function (): void {
    $textarea = new TextareaContentEditor();
    $editorJs = new EditorJsContentEditor($textarea);
    $registry = new ContentEditorRegistry($editorJs, $textarea);
    $selected = $registry->preferred('editorjs', 'textarea');
    $html = $selected->render('content', '<p>Saved HTML</p>', ['label' => 'Content']);

    expect($selected)->toBe($editorJs)
        ->and($html)->toContain('data-zoosper-editor="editorjs"')
        ->toContain('name="content_json"')
        ->toContain('name="content"')
        ->toContain('&lt;p&gt;Saved HTML&lt;/p&gt;');
});

it('uses textarea when runtime selection requests the safe built-in fallback', function (): void {
    $textarea = new TextareaContentEditor();
    $registry = new ContentEditorRegistry(new EditorJsContentEditor($textarea), $textarea);
    $selected = $registry->preferred('textarea', 'editorjs');

    expect($selected)->toBe($textarea)
        ->and($selected->render('content', '<strong>Text</strong>'))
        ->toContain('admin-content-editor--textarea')
        ->toContain('&lt;strong&gt;Text&lt;/strong&gt;');
});

it('selects a module-registered custom editor code without changing the registry', function (): void {
    $custom = new class implements ContentEditorInterface {
        public function code(): string { return 'custom-blocks'; }
        public function render(string $fieldName, string $value, array $context = []): string { return 'custom:' . $fieldName; }
    };
    $textarea = new TextareaContentEditor();
    $registry = new ContentEditorRegistry($textarea);
    $registry->register($custom);

    expect($registry->preferred('custom-blocks', 'textarea'))->toBe($custom)
        ->and($custom->render('content', ''))->toBe('custom:content');
});

it('escapes image tool configuration and omits it when JSON encoding fails', function (): void {
    $previousSession = $_SESSION ?? null;
    $_SESSION = ['_csrf_token' => 'token"<unsafe>'];

    try {
        $config = new class implements EditorImageToolConfigInterface {
            public bool $invalid = false;

            public function toArray(string $csrfToken): array
            {
                return ['token' => $csrfToken, 'value' => $this->invalid ? "\xB1" : '<image>'];
            }
        };
        $editor = new EditorJsContentEditor(new TextareaContentEditor(), $config, new CsrfTokenManager());
        $html = $editor->render('content', '');

        expect($html)->toContain('data-zoosper-image-tool="{&quot;token&quot;:&quot;token\\&quot;&lt;unsafe&gt;&quot;,&quot;value&quot;:&quot;&lt;image&gt;&quot;}"');

        $config->invalid = true;
        expect($editor->render('content', ''))->not->toContain('data-zoosper-image-tool=');
    } finally {
        if ($previousSession === null) {
            unset($_SESSION);
        } else {
            $_SESSION = $previousSession;
        }
    }
});










