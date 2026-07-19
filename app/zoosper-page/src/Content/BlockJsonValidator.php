<?php

declare(strict_types=1);

namespace Zoosper\Page\Content;

/**
 * Validates the supported Editor.js block_json document shape before saving.
 *
 * The validator intentionally accepts only server-rendered block types. Image
 * blocks are allowed only when they point at managed local media URLs so remote
 * URLs cannot be silently persisted and rendered later.
 */
final readonly class BlockJsonValidator
{
    /** @var list<string> */
    private array $allowedTypes;
    private int $maxListDepth;

    /** @param array<string, mixed> $config */
    public function __construct(array $config = [])
    {
        $configuredTypes = $config['allowed_types'] ?? ['paragraph', 'header', 'list', 'image'];
        $types = is_array($configuredTypes)
            ? array_values(array_filter(array_map('strval', $configuredTypes)))
            : ['paragraph', 'header', 'list', 'image'];

        // Phase 1.37m.4/1.37m.5: image blocks are now part of the supported
        // content_json pipeline: upload endpoint, admin runtime and frontend
        // renderer are wired.
        if (!in_array('image', $types, true)) {
            $types[] = 'image';
        }

        $this->allowedTypes = array_values(array_unique($types));
        $this->maxListDepth = max(1, (int) ($config['max_list_depth'] ?? 3));
    }

    /** @param array<string, mixed> $document */
    public function validate(array $document): BlockJsonValidationResult
    {
        $errors = [];
        $blocks = $document['blocks'] ?? null;

        if (!is_array($blocks)) {
            return BlockJsonValidationResult::fail(["Editor's block JSON must contain a blocks array."]);
        }

        foreach ($blocks as $index => $block) {
            if (!is_array($block)) {
                $errors[] = 'Block ' . ($index + 1) . ' must be an object.';
                continue;
            }

            $type = (string) ($block['type'] ?? '');
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];

            if (!in_array($type, $this->allowedTypes, true)) {
                $errors[] = 'Block ' . ($index + 1) . ' has unsupported type: ' . $type . '.';
                continue;
            }

            match ($type) {
                'paragraph' => $this->validateParagraph($data, $index, $errors),
                'header' => $this->validateHeader($data, $index, $errors),
                'list' => $this->validateListBlock($data, $index, $errors),
                'image' => $this->validateImage($data, $index, $errors),
                default => null,
            };
        }

        return $errors === [] ? BlockJsonValidationResult::ok() : BlockJsonValidationResult::fail($errors);
    }

    /** @param array<string, mixed> $data @param list<string> $errors */
    private function validateParagraph(array $data, int $index, array &$errors): void
    {
        if (!array_key_exists('text', $data) || !is_string($data['text'])) {
            $errors[] = 'Block ' . ($index + 1) . ' paragraph text must be a string.';
        }
    }

    /** @param array<string, mixed> $data @param list<string> $errors */
    private function validateHeader(array $data, int $index, array &$errors): void
    {
        if (!array_key_exists('text', $data) || !is_string($data['text'])) {
            $errors[] = 'Block ' . ($index + 1) . ' header text must be a string.';
        }

        $level = (int) ($data['level'] ?? 0);
        if (!in_array($level, [2, 3, 4], true)) {
            $errors[] = 'Block ' . ($index + 1) . ' header level must be 2, 3 or 4.';
        }
    }

    /** @param array<string, mixed> $data @param list<string> $errors */
    private function validateListBlock(array $data, int $index, array &$errors): void
    {
        $style = (string) ($data['style'] ?? 'unordered');
        if (!in_array($style, ['ordered', 'unordered'], true)) {
            $errors[] = 'Block ' . ($index + 1) . ' list style must be ordered or unordered.';
        }

        if (!is_array($data['items'] ?? null)) {
            $errors[] = 'Block ' . ($index + 1) . ' list items must be an array.';
            return;
        }

        $this->validateListItems($data['items'], $index, 1, $errors);
    }

    /** @param array<int, mixed> $items @param list<string> $errors */
    private function validateListItems(array $items, int $blockIndex, int $depth, array &$errors): void
    {
        if ($depth > $this->maxListDepth) {
            $errors[] = 'Block ' . ($blockIndex + 1) . ' list nesting exceeds the maximum depth.';
            return;
        }

        foreach ($items as $item) {
            if (is_string($item)) {
                continue;
            }
            if (!is_array($item)) {
                $errors[] = 'Block ' . ($blockIndex + 1) . ' list item must be a string or object.';
                continue;
            }
            if (!is_string($item['content'] ?? null)) {
                $errors[] = 'Block ' . ($blockIndex + 1) . ' list item content must be a string.';
            }
            if (isset($item['items'])) {
                if (!is_array($item['items'])) {
                    $errors[] = 'Block ' . ($blockIndex + 1) . ' nested list items must be an array.';
                    continue;
                }
                $this->validateListItems($item['items'], $blockIndex, $depth + 1, $errors);
            }
        }
    }

    /** @param array<string, mixed> $data @param list<string> $errors */
    private function validateImage(array $data, int $index, array &$errors): void
    {
        $file = $data['file'] ?? null;
        if (!is_array($file)) {
            $errors[] = 'Block ' . ($index + 1) . ' image file must be an object.';
            return;
        }

        $url = trim((string) ($file['url'] ?? ''));
        if ($url === '' || !str_starts_with($url, '/media/')) {
            $errors[] = 'Block ' . ($index + 1) . ' image URL must use managed /media/ storage.';
        }

        if (isset($data['caption']) && !is_string($data['caption'])) {
            $errors[] = 'Block ' . ($index + 1) . ' image caption must be a string.';
        }

        foreach (['withBorder', 'withBackground', 'stretched'] as $flag) {
            if (isset($data[$flag]) && !is_bool($data[$flag])) {
                $errors[] = 'Block ' . ($index + 1) . ' image flag ' . $flag . ' must be boolean.';
            }
        }
    }
}
