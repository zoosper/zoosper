<?php

declare(strict_types=1);

namespace Zoosper\Core\Editor;

/**
 * Optional editor image-block data boundary supplied by an enabled feature module.
 *
 * Core defines only the editor-facing shape. The contributing module owns URL
 * policy and normalisation, while Page remains responsible for generated HTML.
 */
interface EditorImageBlockSanitizerInterface
{
    /**
     * @param array<string, mixed> $data
     * @return array{url: string, caption: string, withBorder: bool, withBackground: bool, stretched: bool}|null
     */
    public function sanitise(array $data): ?array;
}
