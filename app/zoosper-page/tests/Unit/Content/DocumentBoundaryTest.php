<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Content;

use RuntimeException;
use Zoosper\Page\Content\DocumentNormalizer;
use Zoosper\Page\Content\DocumentValidator;

function phase13B4DocumentNormalizer(): DocumentNormalizer
{
    return new DocumentNormalizer(new DocumentValidator());
}

it('adds and canonically encodes the supported Page document schema version', function (): void {
    $normalizer = phase13B4DocumentNormalizer();
    $document = $normalizer->fromArray([
        'blocks' => [
            ['type' => 'paragraph', 'data' => ['text' => 'Hello']],
        ],
    ]);

    expect($document['schema_version'])->toBe(1)
        ->and($normalizer->encode($document))->toContain('"schema_version":1');
});

it('rejects unsupported Page document schema versions', function (): void {
    expect(fn (): array => phase13B4DocumentNormalizer()->fromArray([
        'schema_version' => 2,
        'blocks' => [],
    ]))->toThrow(RuntimeException::class, 'Unsupported content document schema version 2; expected 1.');
});

it('tolerates malformed historical JSON without accepting it as a document', function (): void {
    expect(phase13B4DocumentNormalizer()->tolerant('{bad'))->toBeNull();
});
