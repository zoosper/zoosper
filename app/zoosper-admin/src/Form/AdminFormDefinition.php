<?php

declare(strict_types=1);

namespace Zoosper\Admin\Form;

final readonly class AdminFormDefinition
{
    /** @param list<AdminFormField> $fields */
    public function __construct(public string $handle, public array $fields)
    {
    }
}
