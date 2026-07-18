<?php

declare(strict_types=1);

namespace Zoosper\Admin\Editor;

use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Media\EditorJs\EditorJsImageToolConfig;

/**
 * Editor.js-backed content editor adapter.
 *
 * The textarea remains the submitted HTML fallback. The hidden `content_json`
 * field stores the structured Editor.js document for future block_json rendering
 * after server-side validation. Optional media tooling is injected by service
 * configuration so the admin package can keep working even when the media module
 * is not installed.
 */
final readonly class EditorJsContentEditor implements ContentEditorInterface
{
    public function __construct(
        private TextareaContentEditor $fallback = new TextareaContentEditor(),
        private ?EditorJsImageToolConfig $imageToolConfig = null,
        private ?CsrfTokenManager $csrf = null,
    ) {
    }

    public function code(): string
    {
        return 'editorjs';
    }

    /** @param array<string, mixed> $context Additional rendering options. */
    public function render(string $fieldName, string $value, array $context = []): string
    {
        $id = 'zoosper-editorjs-' . bin2hex(random_bytes(6));
        $safeId = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars((string) ($context['label'] ?? 'Content'), ENT_QUOTES, 'UTF-8');
        $contentJson = htmlspecialchars((string) ($context['content_json'] ?? ''), ENT_QUOTES, 'UTF-8');
        $imageToolJson = $this->imageToolJson();
        $imageToolAttribute = $imageToolJson !== ''
            ? ' data-zoosper-image-tool="' . htmlspecialchars($imageToolJson, ENT_QUOTES, 'UTF-8') . '"'
            : '';
        $textarea = $this->fallback->render($fieldName, $value, $context);

        return <<<HTML
<div class="zoosper-content-editor" data-zoosper-editor="editorjs"{$imageToolAttribute}>
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

    private function imageToolJson(): string
    {
        if ($this->imageToolConfig === null || $this->csrf === null) {
            return '';
        }

        return json_encode($this->imageToolConfig->toArray($this->csrf->token()), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '';
    }
}
