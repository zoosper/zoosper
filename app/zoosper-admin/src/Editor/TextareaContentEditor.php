<?php

declare(strict_types=1);

namespace Zoosper\Admin\Editor;

/**
 * Safe baseline content editor backed by a standard textarea.
 */
final readonly class TextareaContentEditor implements ContentEditorInterface
{
    public function code(): string
    {
        return 'textarea';
    }

    public function render(string $fieldName, string $value, array $context = []): string
    {
        $name = htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8');
        $content = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $rows = (int) ($context['rows'] ?? 14);
        $required = !empty($context['required']) ? ' required' : '';

        return '<textarea name="' . $name . '" rows="' . $rows . '" class="admin-content-editor admin-content-editor--textarea"' . $required . '>' . $content . '</textarea>';
    }
}
