<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\BulkAction;

use InvalidArgumentException;
use Zoosper\Grid\BulkAction\GridBulkActionRequest;

/** Converts a protected POST form into the shared Grid request contract. */
final readonly class GridBulkHttpRequestParser
{
    public function parse(string $gridKey, GridBulkHttpRequest $request): GridBulkActionRequest
    {
        if (strtoupper($request->method) !== 'POST') {
            throw new InvalidArgumentException('Grid bulk actions require POST.');
        }

        $actionId = trim((string) ($request->form['bulk_action'] ?? ''));
        $selected = $request->form['selected_ids'] ?? [];
        if (!is_array($selected)) {
            throw new InvalidArgumentException('Grid selected identities must be an array.');
        }

        return new GridBulkActionRequest($gridKey, $actionId, array_values($selected));
    }
}
