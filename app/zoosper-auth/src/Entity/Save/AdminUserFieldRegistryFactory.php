<?php

declare(strict_types=1);

namespace Zoosper\Auth\Entity\Save;

use Zoosper\Core\Entity\Save\FieldDefinitionProviderInterface;
use Zoosper\Core\Entity\Save\FieldDefinitionRegistry;

/**
 * Builds the AdminUser field registry from core and module providers.
 *
 * The constructor accepts additional providers so modules can contribute fields
 * without modifying this factory or UserAdminController. Core column providers
 * become part of the safe write map; extension and handler fields remain
 * available to the save context for later processing.
 */
final readonly class AdminUserFieldRegistryFactory
{
    /** @param iterable<FieldDefinitionProviderInterface> $providers */
    public function __construct(private iterable $providers = [])
    {
    }

    public function create(): FieldDefinitionRegistry
    {
        $registry = new FieldDefinitionRegistry();
        $registry->registerMany((new AdminUserFieldDefinitionProvider())->definitions());

        foreach ($this->providers as $provider) {
            $registry->registerMany($provider->definitions());
        }

        return $registry;
    }
}
