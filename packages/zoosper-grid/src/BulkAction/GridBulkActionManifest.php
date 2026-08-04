<?php

declare(strict_types=1);

namespace Zoosper\Grid\BulkAction;

use JsonSerializable;

/** Safe browser-facing representation of actions already authorised by the host. */
final readonly class GridBulkActionManifest implements JsonSerializable
{
    /** @param list<GridBulkActionDefinition> $actions */
    public function __construct(
        public string $gridKey,
        public array $actions,
    ) {
    }

    /** @return array{gridKey: string, actions: list<array<string, bool|int|string|null>>} */
    public function jsonSerialize(): array
    {
        return [
            'gridKey' => $this->gridKey,
            'actions' => array_map(
                static fn (GridBulkActionDefinition $action): array => [
                    'id' => $action->id,
                    'label' => $action->label,
                    'selectionScope' => $action->selectionScope->value,
                    'executionType' => $action->executionType->value,
                    'confirmationPolicy' => $action->confirmationPolicy->value,
                    'maximumSelection' => $action->maximumSelection,
                    'auditRequired' => $action->auditRequired,
                ],
                $this->actions,
            ),
        ];
    }
}
