<?php

declare(strict_types=1);

use Marko\Clock\SystemClock;
use Marko\Config\ConfigRepositoryInterface;
use Marko\Session\Config\SessionConfig;
use Marko\Session\File\Handler\FileSessionHandler;
use Marko\Core\Path\ProjectPaths;
use Psr\Clock\ClockInterface;
use Zoosper\Core\Container\ServiceContainer;

return [
    ClockInterface::class => static fn (): ClockInterface => new SystemClock(),
    SessionHandlerInterface::class => static function (ServiceContainer $services): SessionHandlerInterface {
        return new FileSessionHandler(
            new SessionConfig($services->get(ConfigRepositoryInterface::class)),
            $services->get(ClockInterface::class),
            $services->get(ProjectPaths::class),
        );
    },
];










