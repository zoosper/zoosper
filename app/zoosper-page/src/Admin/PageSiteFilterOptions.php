<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Grid\GridFilterOption;
use Zoosper\Site\Repository\SiteRepository;

/** Builds administrator-friendly Site filter labels from active sites. */
final readonly class PageSiteFilterOptions
{
    public function __construct(private SiteRepository $sites)
    {
    }

    /** @return list<GridFilterOption> */
    public function all(): array
    {
        $options = [];
        foreach ($this->sites->allActive() as $site) {
            $id = $site->id;
            $name = trim($site->name);
            if ($id > 0 && $name !== '') {
                $options[] = new GridFilterOption((string) $id, $name);
            }
        }
        return $options;
    }
}










