<?php

declare(strict_types=1);

namespace Zoosper\Page\Content;

/**
 * Validates the restricted block JSON shape Zoosper currently supports.
 *
 * Phase 0.76 supports paragraph, header and list blocks only. Validation is
 * intentionally strict so future persistence can reject unknown/unsafe blocks.
 */
final readonly class BlockJsonValidator
{
    /** @param array<string, mixed> $config */
    public function __construct(private array $config = [])
    {
    }

    /** @param array<string, mixed> $document */
    public function validate(array $document): BlockJsonValidationResult
    {
        $errors = [];
        if (!isset($document['blocks']) || !is_array($document['blocks'])) {
            return BlockJsonValidationResult::fail(['Document must contain a blocks array.']);
        }

        foreach ($document['blocks'] as $index => $block) {
            if (!is_array($block)) {
                $errors[] = "Block {$index} must be an object.";
                continue;
            }

            $type = (string) ($block['type'] ?? '');
            $data = $block['data'] ?? null;
            if (!is_array($data)) {
                $errors[] = "Block {$index} must contain data.";
                continue;
            }

            match ($type) {
                'paragraph' => $this->validateParagraph($index, $data, $errors),
                'header' => $this->validateHeader($index, $data, $errors),
                'list' => $this->validateList($index, $data, $errors),
                default => $errors[] = "Block {$index} has unsupported type: {$type}.",
            };
        }

        return $errors === [] ? BlockJsonValidationResult::ok() : BlockJsonValidationResult::fail($errors);
    }

    /** @param array<string, mixed> $data @param list<string> $errors */
    private function validateParagraph(int $index, array $data, array &$errors): void
    {
        if (!isset($data['text']) || !is_string($data['text'])) {
            $errors[] = "Paragraph block {$index} requires string data.text.";
        }
    }

    /** @param array<string, mixed> $data @param list<string> $errors */
    private function validateHeader(int $index, array $data, array &$errors): void
    {
        if (!isset($data['text']) || !is_string($data['text'])) {
            $errors[] = "Header block {$index} requires string data.text.";
        }

        $allowed = $this->config['allowed_heading_levels'] ?? [2, 3, 4];
        $level = (int) ($data['level'] ?? 0);
        if (!in_array($level, $allowed, true)) {
            $errors[] = "Header block {$index} level must be one of: " . implode(', ', $allowed) . '.';
        }
    }

    /** @param array<string, mixed> $data @param list<string> $errors */
    private function validateList(int $index, array $data, array &$errors): void
    {
        $style = (string) ($data['style'] ?? '');
        if (!in_array($style, ['ordered', 'unordered'], true)) {
            $errors[] = "List block {$index} style must be ordered or unordered.";
        }

        if (!isset($data['items']) || !is_array($data['items'])) {
            $errors[] = "List block {$index} requires data.items array.";
            return;
        }

        $this->validateListItems($data['items'], $errors, "List block {$index}", 1);
    }

    /** @param array<int, mixed> $items @param list<string> $errors */
    private function validateListItems(array $items, array &$errors, string $prefix, int $depth): void
    {
        $maxDepth = (int) ($this->config['max_list_depth'] ?? 3);
        if ($depth > $maxDepth) {
            $errors[] = "{$prefix} exceeds max nesting depth {$maxDepth}.";
            return;
        }

        foreach ($items as $itemIndex => $item) {
            if (!is_array($item)) {
                $errors[] = "{$prefix} item {$itemIndex} must be an object.";
                continue;
            }

            if (!isset($item['content']) || !is_string($item['content'])) {
                $errors[] = "{$prefix} item {$itemIndex} requires string content.";
            }

            if (isset($item['items']) && is_array($item['items'])) {
                $this->validateListItems($item['items'], $errors, "{$prefix} item {$itemIndex}", $depth + 1);
            }
        }
    }
}
