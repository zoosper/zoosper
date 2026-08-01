<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Grid\GridFilter;

/** Creates the named Site multiselect for the Pages definition. */
final readonly class PageGridSiteFilter
{
    public function __construct(private PageSiteFilterOptions $options)
    {
    }

    public function build(): GridFilter
    {
        return new GridFilter(
            key: 'site_id',
            label: 'Site',
            type: 'multiselect',
            options: $this->options->all(),
        );
    }
}
