<?php

declare(strict_types=1);

use Marko\Config\ConfigRepositoryInterface;
use Marko\Session\Config\SessionConfig;
use Marko\Session\File\Handler\FileSessionHandler;
use Zoosper\Core\Container\ServiceContainer;

return [
    SessionHandlerInterface::class => static function (ServiceContainer $services): SessionHandlerInterface {
        return new FileSessionHandler(
            new SessionConfig($services->get(ConfigRepositoryInterface::class)),
        );
    },
];










