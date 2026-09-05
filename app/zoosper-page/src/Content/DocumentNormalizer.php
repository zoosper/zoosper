<?php
declare(strict_types=1);

namespace Zoosper\Page\Content;

use JsonException;
use RuntimeException;
/** Single decode, version, validation and canonical encoding boundary. */
final readonly class DocumentNormalizer
{
    public function __construct(private DocumentValidator $validator) {}
    public function fromJson(?string $json): ?array
    {
        $json = trim((string) $json);
        if ($json === '') {
            return null;
        }
        try { $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR); }
        catch (JsonException $e) { throw new RuntimeException('Invalid Editor.js JSON payload.', previous: $e); }
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid Editor.js JSON payload.');
        }
        return $this->fromArray($decoded);
    }
    /** @param array<string,mixed> $document @return array<string,mixed> */
    public function fromArray(array $document): array
    {
        $document['schema_version'] = (int) ($document['schema_version'] ?? $this->validator->schemaVersion());
        $document['blocks'] = array_values(is_array($document['blocks'] ?? null) ? $document['blocks'] : []);
        $this->validator->validate($document);
        return $document;
    }
    public function tolerant(?string $json): ?array
    {
        try {
            return $this->fromJson($json);
        } catch (RuntimeException) {
            return null;
        }
    }
    /** @param array<string,mixed>|null $document */
    public function encode(?array $document): ?string
    {
        if ($document === null) {
            return null;
        }
        return json_encode($this->fromArray($document), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
