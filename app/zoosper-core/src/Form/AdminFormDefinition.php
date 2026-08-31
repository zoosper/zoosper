<?php

declare(strict_types=1);

namespace Zoosper\Core\Form;

final readonly class AdminFormDefinition
{
    /**
     * @param list<AdminFormField> $fields
     * @param array<string, array{title: string, description?: string}> $sections
     */
    public function __construct(
        public string $handle,
        public array $fields,
        public array $sections = []
    ) {
    }
}
