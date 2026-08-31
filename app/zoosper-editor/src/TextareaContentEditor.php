<?php

declare(strict_types=1);

namespace Zoosper\Editor;

use Zoosper\Core\Editor\ContentEditorInterface;

/**
 * Safe baseline content editor backed by a standard textarea.
 */
final readonly class TextareaContentEditor implements ContentEditorInterface
{
    public function code(): string
    {
        return 'textarea';
    }

    /** @param array<string, mixed> $context */
    public function render(string $fieldName, string $value, array $context = []): string
    {
        $id = 'zoosper-editor-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $fieldName);
        $safeId = htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8');
        $safeName = htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars((string) ($context['label'] ?? 'Content'), ENT_QUOTES, 'UTF-8');
        $rows = (int) ($context['rows'] ?? 16);
        $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<div class="field admin-content-editor--textarea">
    <label for="{$safeId}">{$label}</label>
    <textarea id="{$safeId}" name="{$safeName}" rows="{$rows}">{$escapedValue}</textarea>
</div>
HTML;
    }
}










