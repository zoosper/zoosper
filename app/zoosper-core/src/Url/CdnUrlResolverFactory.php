<?php

declare(strict_types=1);

namespace Zoosper\Core\Url;

use Zoosper\Core\Config\ConfigRepository;

/**
 * Creates CdnUrlResolver from the central configuration repository.
 *
 * Keeping construction in a factory makes it easy to register the resolver in
 * the service container now and later replace it with a site/store-scoped
 * implementation without changing consumers.
 */
final readonly class CdnUrlResolverFactory
{
    public function __construct(private ConfigRepository $config)
    {
    }

    /**
     * Create the resolver from config/cdn.php.
     */
    public function create(): CdnUrlResolver
    {
        return new CdnUrlResolver($this->config->array('cdn'));
    }
}
