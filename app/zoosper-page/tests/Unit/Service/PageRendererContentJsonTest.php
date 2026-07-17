<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Service;

use Zoosper\Page\Content\BlockJsonToHtmlRenderer;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Service\PageRenderer;

function phase136Page(string $format, ?string $json, string $html = '<p>HTML fallback</p>'): Page
{
    return new Page(
        id: 1,
        siteId: 1,
        title: 'Home',
        slug: 'home',
        content: $html,
        status: 'published',
        contentFormat: $format,
        contentJson: $json,
    );
}

test('PageRenderer uses saved HTML for normal html pages', function () {
    $page = phase136Page('html', null, '<p>Existing sanitised HTML</p>');

    expect((new PageRenderer(blockJsonRenderer: new BlockJsonToHtmlRenderer()))->renderContent($page))
        ->toBe('<p>Existing sanitised HTML</p>');
});

test('PageRenderer renders block_json pages from content_json', function () {
    $page = phase136Page('block_json', json_encode([
        'blocks' => [
            ['type' => 'header', 'data' => ['text' => 'Block heading', 'level' => 2]],
            ['type' => 'paragraph', 'data' => ['text' => 'Block paragraph']],
        ],
    ], JSON_THROW_ON_ERROR));

    $html = (new PageRenderer(blockJsonRenderer: new BlockJsonToHtmlRenderer()))->renderContent($page);

    expect($html)->toContain('<h2>Block heading</h2>');
    expect($html)->toContain('<p>Block paragraph</p>');
});

test('PageRenderer falls back to HTML when block_json cannot be decoded', function () {
    $page = phase136Page('block_json', '{not valid json}', '<p>Safe fallback</p>');

    expect((new PageRenderer(blockJsonRenderer: new BlockJsonToHtmlRenderer()))->renderContent($page))
        ->toBe('<p>Safe fallback</p>');
});
