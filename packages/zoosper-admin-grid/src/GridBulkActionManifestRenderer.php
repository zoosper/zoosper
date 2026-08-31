<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

use JsonException;
use Zoosper\Grid\BulkAction\GridBulkActionManifest;

/** Renders an inert JSON manifest for the shared Admin Grid browser controller. */
final readonly class GridBulkActionManifestRenderer
{
    /** @throws JsonException */
    public function render(GridBulkActionManifest $manifest): string
    {
        $json = json_encode(
            $manifest,
            JSON_THROW_ON_ERROR
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
            | JSON_UNESCAPED_SLASHES,
        );

        return '<script type="application/json" data-grid-bulk-action-manifest>'
            . $json
            . '</script>';
    }
}











