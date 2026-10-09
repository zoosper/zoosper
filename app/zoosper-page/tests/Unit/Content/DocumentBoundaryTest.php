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

    expect($document->structured['schema_version'])->toBe(1)
        ->and($document->isStructured())->toBeTrue()
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


it('retains configured schema versions and rejects mismatches through the Page boundary', function (): void {
    $config = \Zoosper\Core\Config\ConfigRepository::fromArray([
        'content_model' => ['block_json' => ['schema_version' => '3', 'allowed_types' => ['paragraph']]],
    ]);
    $validator = new DocumentValidator($config);
    $normalizer = new DocumentNormalizer($validator);
    expect($validator->schemaVersion())->toBe(3)
        ->and($normalizer->fromArray(['blocks' => []])->structured['schema_version'])->toBe(3);
    expect(fn () => $validator->validate(['schema_version' => 2, 'blocks' => []]))
        ->toThrow(RuntimeException::class, 'Unsupported content document schema version 2; expected 3.');
    expect(fn () => $validator->validate(['schema_version' => 3, 'blocks' => [['type' => 'unsupported', 'data' => []]]]))
        ->toThrow(RuntimeException::class, 'Invalid Editor.js JSON payload:');
    foreach ([[], ['content_model' => ['block_json' => 'not-an-array']]] as $items) {
        expect((new DocumentValidator(\Zoosper\Core\Config\ConfigRepository::fromArray($items)))->schemaVersion())->toBe(1);
    }
});
