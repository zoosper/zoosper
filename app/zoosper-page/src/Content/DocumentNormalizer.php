<?php

declare(strict_types=1);

namespace Zoosper\Page\Content;

use JsonException;
use RuntimeException;

/** Single decode, version, validation and canonical encoding boundary. */
final readonly class DocumentNormalizer
{
    public function __construct(private DocumentValidator $validator)
    {
    }

    public function fromJson(?string $json, string $html = ''): ?PageContentDocument
    {
        $json = trim((string) $json);
        if ($json === '') {
            return null;
        }

        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Invalid Editor.js JSON payload.', previous: $exception);
        }

        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid Editor.js JSON payload.');
        }

        return $this->fromArray($decoded, $html);
    }

    /** @param array<string, mixed> $document */
    public function fromArray(array $document, string $html = ''): PageContentDocument
    {
        $document['schema_version'] = (int) ($document['schema_version'] ?? $this->validator->schemaVersion());
        $document['blocks'] = array_values(is_array($document['blocks'] ?? null) ? $document['blocks'] : []);
        $this->validator->validate($document);

        return PageContentDocument::structured($document, $html);
    }

    public function tolerant(?string $json, string $html = ''): ?PageContentDocument
    {
        try {
            return $this->fromJson($json, $html);
        } catch (RuntimeException) {
            return null;
        }
    }

    public function encode(?PageContentDocument $document): ?string
    {
        if ($document === null || !$document->isStructured()) {
            return null;
        }

        return json_encode(
            $document->structured,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );
    }
}
