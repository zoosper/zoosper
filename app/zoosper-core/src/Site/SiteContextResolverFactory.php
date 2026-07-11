<?php

declare(strict_types=1);

namespace Zoosper\Core\Site;

use Zoosper\Core\Config\ConfigRepository;

/**
 * Creates the SiteContextResolver from config/sites.php.
 */
final readonly class SiteContextResolverFactory
{
    public function __construct(private ConfigRepository $config)
    {
    }

    /**
     * Create a resolver using site/store/store-view configuration.
     */
    public function create(): SiteContextResolver
    {
        return new SiteContextResolver($this->config->array('sites'));
    }
}
