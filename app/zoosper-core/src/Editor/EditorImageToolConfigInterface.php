<?php

declare(strict_types=1);

namespace Zoosper\Core\Editor;

/**
 * Supplies optional image-tool runtime configuration to a content editor.
 *
 * Feature modules may implement this boundary without making the editor depend
 * on their concrete classes. The returned data is rendered as inert JSON and
 * interpreted only by the selected editor runtime.
 */
interface EditorImageToolConfigInterface
{
    /** @return array<string, mixed> */
    public function toArray(string $csrfToken): array;
}
