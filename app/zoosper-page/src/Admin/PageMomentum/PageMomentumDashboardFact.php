<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\PageMomentum;

/**
 * Immutable read-only dashboard fact used by the page momentum dashboard.
 *
 * These facts are intentionally simple value objects so the dashboard can
 * display useful operational information without coupling the controller
 * directly to persistence details.
 */
final readonly class PageMomentumDashboardFact
{
    public function __construct(
        public string $key,
        public string $label,
        public int $value,
        public string $description,
        public string $status = 'neutral',
    ) {
    }

    /**
     * @return array{key:string,label:string,value:int,description:string,status:string}
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'value' => $this->value,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }
}
