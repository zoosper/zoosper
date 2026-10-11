<?php

declare(strict_types=1);

namespace Zoosper\Page\Content;

use Zoosper\Core\Editor\EditorImageBlockValidatorInterface;

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
    public function __construct(
        array $config = [],
        private ?EditorImageBlockValidatorInterface $imageValidator = null,
    ) {
        $types = $this->stringList($config['allowed_types'] ?? null)
            ?? ['paragraph', 'header', 'list', 'image'];

        if ($this->imageValidator !== null && !in_array('image', $types, true)) {
            $types[] = 'image';
        }

        $this->allowedTypes = array_values(array_unique($types));
        $this->maxListDepth = $this->positiveInt($config['max_list_depth'] ?? null, 3);
    }

    /** @param array<string, mixed> $document */
    public function validate(array $document): BlockJsonValidationResult
    {
        /** @var list<string> $errors */
        $errors = [];
        $blocks = $document['blocks'] ?? null;

        if (!is_array($blocks) || !array_is_list($blocks)) {
            return BlockJsonValidationResult::fail(["Editor's block JSON must contain a blocks list."]);
        }

        for ($index = 0, $count = count($blocks); $index < $count; $index++) {
            $block = $this->stringMap($blocks[$index]);
            if ($block === null) {
                $errors[] = 'Block ' . ($index + 1) . ' must be an object.';
                continue;
            }

            $type = $this->stringValue($block['type'] ?? null, '');
            $data = $this->stringMap($block['data'] ?? null) ?? [];

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

        return $errors === []
            ? BlockJsonValidationResult::ok()
            : BlockJsonValidationResult::fail($this->stringList($errors) ?? []);
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
        $style = $this->stringValue($data['style'] ?? null, 'unordered');
        if (!in_array($style, ['ordered', 'unordered'], true)) {
            $errors[] = 'Block ' . ($index + 1) . ' list style must be ordered or unordered.';
        }

        $items = $data['items'] ?? null;
        if (!is_array($items) || !array_is_list($items)) {
            $errors[] = 'Block ' . ($index + 1) . ' list items must be a list.';
            return;
        }

        $this->validateListItems($items, $index, 1, $errors);
    }

    /** @param list<mixed> $items @param list<string> $errors */
    private function validateListItems(array $items, int $blockIndex, int $depth, array &$errors): void
    {
        if ($depth > $this->maxListDepth) {
            $errors[] = 'Block ' . ($blockIndex + 1) . ' list nesting exceeds the maximum depth.';
            return;
        }

        for ($index = 0, $count = count($items); $index < $count; $index++) {
            if (is_string($items[$index])) {
                continue;
            }

            $item = $this->stringMap($items[$index]);
            if ($item === null) {
                $errors[] = 'Block ' . ($blockIndex + 1) . ' list item must be a string or object.';
                continue;
            }
            if (!is_string($item['content'] ?? null)) {
                $errors[] = 'Block ' . ($blockIndex + 1) . ' list item content must be a string.';
            }
            if (array_key_exists('items', $item)) {
                $nestedItems = $item['items'];
                if (!is_array($nestedItems) || !array_is_list($nestedItems)) {
                    $errors[] = 'Block ' . ($blockIndex + 1) . ' nested list items must be a list.';
                    continue;
                }
                $this->validateListItems($nestedItems, $blockIndex, $depth + 1, $errors);
            }
        }
    }

    /** @param array<string, mixed> $data @param list<string> $errors */
    private function validateImage(array $data, int $index, array &$errors): void
    {
        if ($this->imageValidator === null) {
            $errors[] = 'Block ' . ($index + 1) . ' has unsupported type: image.';
            return;
        }

        foreach ($this->imageValidator->validate($data) as $error) {
            $errors[] = 'Block ' . ($index + 1) . ' ' . $error;
        }
    }

    /** @return list<string>|null */
    private function stringList(mixed $value): ?array
    {
        if (!is_array($value) || !array_is_list($value)) {
            return null;
        }

        $strings = [];
        for ($index = 0, $count = count($value); $index < $count; $index++) {
            if (!is_string($value[$index]) || trim($value[$index]) === '') {
                return null;
            }
            $strings[] = $value[$index];
        }

        return $strings;
    }

    /** @return array<string, mixed>|null */
    private function stringMap(mixed $value): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        $map = [];
        foreach (array_keys($value) as $key) {
            if (!is_string($key)) {
                return null;
            }
            // Values remain intentionally mixed; only object keys are validated here.
            /** @var mixed */
            $map[$key] = $value[$key];
        }

        return $map;
    }

    private function stringValue(mixed $value, string $default): string
    {
        return is_string($value) ? $value : $default;
    }

    private function positiveInt(mixed $value, int $default): int
    {
        if (is_int($value)) {
            return max(1, $value);
        }
        if (is_string($value) && preg_match('/^[0-9]+$/D', $value) === 1) {
            return max(1, (int) $value);
        }

        return $default;
    }
}
