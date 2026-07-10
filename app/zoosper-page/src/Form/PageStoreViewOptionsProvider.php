<?php

declare(strict_types=1);

namespace Zoosper\Page\Form;

use Zoosper\Site\Repository\SiteRepository;

/**
 * Builds option payloads for the page store-view tag selector.
 *
 * The provider converts active site/store-view records into a simple option
 * array that can be consumed by the shared admin tag-selector component. The
 * payload contains only non-sensitive site labels and IDs.
 */
final readonly class PageStoreViewOptionsProvider
{
    public function __construct(private SiteRepository $sites)
    {
    }

    /**
     * Return active site/store-view options for the tag selector component.
     *
     * @return list<array{id:int,label:string,description:string}>
     */
    public function options(): array
    {
        $options = [];

        foreach ($this->sites->allActive() as $site) {
            $options[] = [
                'id' => (int) $site->id,
                'label' => (string) $site->name,
                'description' => (string) $site->code,
            ];
        }

        return $options;
    }
}
