<?php
declare(strict_types=1);

namespace Zoosper\Page\Content;

use RuntimeException;
use Zoosper\Core\Config\ConfigRepository;
/** Canonical Page-owned validation boundary for stored structured documents. */
final readonly class DocumentValidator
{
    public function __construct(private ?ConfigRepository $config = null) {}
    /** @param array<string,mixed> $document */
    public function validate(array $document): void
    {
        $model = $this->config?->array('content_model') ?? [];
        $block = is_array($model['block_json'] ?? null) ? $model['block_json'] : [];
        $expected = max(1, (int) ($block['schema_version'] ?? 1));
        $actual = (int) ($document['schema_version'] ?? $expected);
        if ($actual !== $expected) {
            throw new RuntimeException(sprintf('Unsupported content document schema version %d; expected %d.', $actual, $expected));
        }
        $result = (new BlockJsonValidator($block))->validate($document);
        if (!$result->valid) {
            throw new RuntimeException('Invalid Editor.js JSON payload: ' . implode(' ', $result->errors));
        }
    }
    public function schemaVersion(): int
    {
        $model = $this->config?->array('content_model') ?? [];
        $block = is_array($model['block_json'] ?? null) ? $model['block_json'] : [];
        return max(1, (int) ($block['schema_version'] ?? 1));
    }
}
