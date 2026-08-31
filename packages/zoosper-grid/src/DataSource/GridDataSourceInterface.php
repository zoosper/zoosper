<?php

declare(strict_types=1);

namespace Zoosper\Grid\DataSource;

/**
 * Neutral collection boundary consumed by Grid page builders.
 *
 * Implementations may read from a database, an HTTP API, a search service or
 * any other collection source. Rendering and transport concerns stay outside
 * this contract.
 */
interface GridDataSourceInterface
{
    public function capabilities(): GridDataSourceCapabilities;

    public function fetch(GridQuery $query): GridResult;
}











