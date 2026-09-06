<?php

declare(strict_types=1);

namespace Zoosper\Core\Editor;

/** Optional feature-owned validation for Editor.js image block data. */
interface EditorImageBlockValidatorInterface
{
    /**
     * @param array<string, mixed> $data
     * @return list<string>
     */
    public function validate(array $data): array;
}
