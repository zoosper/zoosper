<?php

declare(strict_types=1);

namespace Zoosper\Grid\BulkAction;

use InvalidArgumentException;

/** Resolves feature-owned executors without coupling Grid core to a module. */
final class GridBulkActionExecutorRegistry
{
    /** @var array<string, GridBulkActionExecutorInterface> */
    private array $executors = [];

    public function register(GridBulkActionExecutorInterface $executor): void
    {
        $key = $this->key($executor->gridKey(), $executor->actionId());
        if (isset($this->executors[$key])) {
            throw new InvalidArgumentException(
                sprintf('Grid bulk-action executor already registered for "%s".', $key),
            );
        }
        $this->executors[$key] = $executor;
    }

    public function require(string $gridKey, string $actionId): GridBulkActionExecutorInterface
    {
        $key = $this->key($gridKey, $actionId);
        return $this->executors[$key]
            ?? throw new InvalidArgumentException(
                sprintf('No Grid bulk-action executor is registered for "%s".', $key),
            );
    }

    private function key(string $gridKey, string $actionId): string
    {
        return trim($gridKey) . ':' . trim($actionId);
    }
}











