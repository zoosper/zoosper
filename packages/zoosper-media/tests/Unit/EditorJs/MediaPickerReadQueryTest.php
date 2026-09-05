<?php

declare(strict_types=1);

use Zoosper\Core\Http\Request;
use Zoosper\Media\EditorJs\MediaPickerReadQuery;

it('normalises bounded picker search and pagination controls', function (): void {
    $query = MediaPickerReadQuery::fromRequest(new Request('GET', '/admin/media/editorjs/library', query: [
        'page' => '999999999',
        'page_size' => '999',
        'q' => ' product ',
        'status' => 'archived',
        'mime_type' => 'application/pdf',
    ]));

    expect($query->pager->page)->toBe(100_000)
        ->and($query->pager->pageSize)->toBe(100)
        ->and($query->query)->toBe('product');
});
