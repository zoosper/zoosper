<?php

declare(strict_types=1);

namespace Zoosper\Admin\Editor;

/**
 * Editor.js-backed content editor adapter.
 *
 * The textarea remains the submitted HTML fallback. The hidden `content_json`
 * field stores the structured Editor.js document for future block_json
 * rendering after server-side validation.
 */
final readonly class EditorJsContentEditor implements ContentEditorInterface
{
    public function __construct(private TextareaContentEditor $fallback = new TextareaContentEditor())
    {
    }

    /**
     * Return the registry code used by the content editor registry.
     */
    public function code(): string
    {
        return 'editorjs';
    }

    /**
     * Render the enhanced Editor.js shell with a safe textarea fallback.
     *
     * @param array<string, mixed> $context Additional rendering options.
     */
    public function render(string $fieldName, string $value, array $context = []): string
    {
        $id = 'zoosper-editorjs-' . bin2hex(random_bytes(6));
        $safeId = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars((string) ($context['label'] ?? 'Content'), ENT_QUOTES, 'UTF-8');
        $contentJson = htmlspecialchars((string) ($context['content_json'] ?? ''), ENT_QUOTES, 'UTF-8');
        $textarea = $this->fallback->render($fieldName, $value, $context);

        return <<<HTML
<div class="zoosper-content-editor" data-zoosper-editor="editorjs">
    <div class="zoosper-content-editor__toolbar">
        <strong>{$label}</strong>
        <span class="zoosper-content-editor__status">Editor.js adapter ready.</span>
    </div>
    <input type="hidden" name="content_json" value="{$contentJson}" data-zoosper-editor-json>
    <div id="{$safeId}" class="zoosper-content-editor__holder" aria-hidden="true"></div>
    {$textarea}
</div>
HTML;
    }
}
