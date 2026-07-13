<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Save;

/**
 * Provides field definitions for an entity/form save pipeline.
 *
 * Core and third-party modules can implement this contract to contribute fields
 * without changing controllers. Save pipelines collect providers, build a field
 * registry, then generate safe write maps for core tables and extension data.
 */
interface FieldDefinitionProviderInterface
{
    /**
     * @return iterable<FieldDefinition>
     */
    public function definitions(): iterable;
}
