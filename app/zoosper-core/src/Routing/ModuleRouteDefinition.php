<?php

declare(strict_types=1);

namespace Zoosper\Core\Routing;

final readonly class ModuleRouteDefinition
{
    public function __construct(
        public string $method,
        public string $path,
        public string $controller,
        public string $action,
        public ?string $permission = null,
        public bool $public = false,
    ) {
    }
}
