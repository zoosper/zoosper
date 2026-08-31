<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Page;

use InvalidArgumentException;
use Zoosper\ApiGrid\Definition\ApiGridRegistry;
use Zoosper\Grid\DataSource\GridDataSourceInterface;

/** Resolves a registered definition and composes data required by an admin page. */
final readonly class ApiGridPageBuilder
{
    /** @param callable(string): object $serviceResolver */
    public function __construct(
        private ApiGridRegistry $registry,
        private ApiGridQueryFactory $queryFactory,
        private mixed $serviceResolver,
    ) {
        if (!is_callable($serviceResolver)) {
            throw new InvalidArgumentException('API Grid service resolver must be callable.');
        }
    }

    /** @param array<string, mixed> $requestValues */
    public function build(string $key, array $requestValues): ApiGridPage
    {
        $definition = $this->registry->get($key);
        $source = ($this->serviceResolver)($definition->dataSourceService);
        if (!$source instanceof GridDataSourceInterface) {
            throw new InvalidArgumentException(
                "API Grid data-source service must implement GridDataSourceInterface: {$definition->dataSourceService}",
            );
        }

        $capabilities = $source->capabilities();
        $query = $this->queryFactory->fromValues($requestValues, $definition, $capabilities);

        return new ApiGridPage($definition, $query, $source->fetch($query), $capabilities);
    }
}











