<?php

declare(strict_types=1);

namespace Zoosper\Page\Content;

/**
 * Converts supported block JSON into conservative HTML.
 *
 * The generated HTML should still pass through the existing HTML sanitizer
 * before persistence or frontend output where appropriate.
 */
final readonly class BlockJsonToHtmlRenderer
{
    /** @param array<string, mixed> $document */
    public function render(array $document): string
    {
        $blocks = is_array($document['blocks'] ?? null) ? $document['blocks'] : [];
        $html = [];

        foreach ($blocks as $block) {
            if (!is_array($block)) {
                continue;
            }

            $type = (string) ($block['type'] ?? '');
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];
            $fragment = match ($type) {
                'paragraph' => $this->paragraph($data),
                'header' => $this->header($data),
                'list' => $this->list($data),
                default => '',
            };

            if ($fragment !== '') {
                $html[] = $fragment;
            }
        }

        return implode("\n", $html);
    }

    /** @param array<string, mixed> $data */
    private function paragraph(array $data): string
    {
        $text = trim((string) ($data['text'] ?? ''));
        return $text === '' ? '' : '<p>' . $text . '</p>';
    }

    /** @param array<string, mixed> $data */
    private function header(array $data): string
    {
        $text = trim((string) ($data['text'] ?? ''));
        $level = in_array((int) ($data['level'] ?? 2), [2, 3, 4], true) ? (int) $data['level'] : 2;
        return $text === '' ? '' : '<h' . $level . '>' . $text . '</h' . $level . '>';
    }

    /** @param array<string, mixed> $data */
    private function list(array $data): string
    {
        $tag = (string) ($data['style'] ?? 'unordered') === 'ordered' ? 'ol' : 'ul';
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];
        $itemsHtml = $this->listItems($items);
        return $itemsHtml === '' ? '' : '<' . $tag . '>' . $itemsHtml . '</' . $tag . '>';
    }

    /** @param array<int, mixed> $items */
    private function listItems(array $items): string
    {
        $html = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $content = trim((string) ($item['content'] ?? ''));
            $nested = is_array($item['items'] ?? null) ? $this->listItems($item['items']) : '';
            $nestedHtml = $nested !== '' ? '<ul>' . $nested . '</ul>' : '';
            if ($content !== '' || $nestedHtml !== '') {
                $html[] = '<li>' . $content . $nestedHtml . '</li>';
            }
        }

        return implode('', $html);
    }
}
