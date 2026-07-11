<?php

declare(strict_types=1);

namespace Zoosper\Admin\Editor;

/**
 * Editor.js-oriented adapter with graceful textarea fallback.
 *
 * Phase 0.68 does not bundle the Editor.js library yet. This adapter provides a
 * stable server-side rendering hook and keeps the textarea as source of truth so
 * ordinary POST/save and HTML sanitisation continue to work. A future npm/Vite
 * phase can load Editor.js locally and enhance the same markup.
 */
final readonly class EditorJsContentEditor implements ContentEditorInterface
{
    public function __construct(private TextareaContentEditor $fallback = new TextareaContentEditor())
    {
    }

    public function code(): string
    {
        return 'editorjs';
    }

    public function render(string $fieldName, string $value, array $context = []): string
    {
        $textarea = $this->fallback->render($fieldName, $value, $context);
        $id = 'zoosper-editor-' . preg_replace('/[^a-z0-9_\-]+/i', '-', $fieldName);
        $id = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars((string) ($context['label'] ?? 'Content'), ENT_QUOTES, 'UTF-8');

        return '<div class="zoosper-content-editor" data-zoosper-editor="editorjs" data-editor-holder="' . $id . '">'
            . '<div class="zoosper-content-editor__toolbar"><span>' . $label . '</span><span class="zoosper-content-editor__status">Textarea fallback active</span></div>'
            . '<div id="' . $id . '" class="zoosper-content-editor__holder" aria-hidden="true"></div>'
            . $textarea
            . '</div>';
    }
}
