<?php

declare(strict_types=1);

namespace Zoosper\Media\EditorJs;

use Zoosper\Core\Http\Request;
use Zoosper\Pagination\Pager;

/** Bounded, server-owned query controls for the Admin Media image picker. */
final readonly class MediaPickerReadQuery
{
    public function __construct(
        public Pager $pager,
        public ?string $query = null,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $query = trim((string) $request->query('q', ''));

        return new self(
            pager: Pager::fromQuery([
                'page' => $request->query('page', '1'),
                'page_size' => $request->query('page_size', '20'),
            ]),
            query: $query === '' ? null : substr($query, 0, 200),
        );
    }
}
