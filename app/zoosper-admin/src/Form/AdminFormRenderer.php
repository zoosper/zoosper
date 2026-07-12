<?php

declare(strict_types=1);

namespace Zoosper\Admin\Form;

/**
 * Renders a sectioned admin form from registered form sections.
 */
final readonly class AdminFormRenderer
{
    /**
     * @param iterable<AdminFormSection> $sections
     */
    public function render(string $action, string $csrfToken, iterable $sections, string $class = 'page-form page-form--sectioned'): string
    {
        $html = '<form method="post" action="' . $this->escape($action) . '" class="' . $this->escape($class) . '">' . PHP_EOL;
        $html .= '    <input type="hidden" name="_csrf_token" value="' . $this->escape($csrfToken) . '">' . PHP_EOL;

        foreach ($sections as $section) {
            $html .= $this->renderSection($section);
        }

        $html .= '</form>';

        return $html;
    }

    private function renderSection(AdminFormSection $section): string
    {
        $modifier = $section->modifierClass ?: 'page-form__section--' . $this->normaliseModifier($section->key);
        $headingId = $this->normaliseModifier($section->key) . '-heading';
        $description = $section->description !== null
            ? '            <p class="muted">' . $this->escape($section->description) . '</p>' . PHP_EOL
            : '';

        return PHP_EOL
            . '    <section class="card page-form__section ' . $this->escape($modifier) . '" aria-labelledby="' . $this->escape($headingId) . '">' . PHP_EOL
            . '        <header class="page-form__section-header">' . PHP_EOL
            . '            <h2 id="' . $this->escape($headingId) . '">' . $this->escape($section->title) . '</h2>' . PHP_EOL
            . $description
            . '        </header>' . PHP_EOL
            . $section->html . PHP_EOL
            . '    </section>' . PHP_EOL;
    }

    private function normaliseModifier(string $key): string
    {
        return trim((string) preg_replace('/[^a-z0-9]+/i', '-', strtolower($key)), '-');
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
