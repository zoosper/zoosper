<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Content;

use Zoosper\Page\Content\ContentFormat;
use Zoosper\Page\Content\PageContentDocument;

it('represents validated structured Page content without losing the HTML bridge', function (): void {
    $document = PageContentDocument::structured(
        ['schema_version' => 1, 'blocks' => []],
        '<p>Fallback</p>',
    );

    expect($document->format)->toBe(ContentFormat::BlockJson)
        ->and($document->html)->toBe('<p>Fallback</p>')
        ->and($document->isStructured())->toBeTrue()
        ->and($document->toApiValue())->toBe(['schema_version' => 1, 'blocks' => []]);
});

it('represents HTML-only Page content without inventing structured data', function (): void {
    $document = PageContentDocument::html('<p>HTML</p>');

    expect($document->format)->toBe(ContentFormat::Html)
        ->and($document->isStructured())->toBeFalse()
        ->and($document->toApiValue())->toBeNull();
});
