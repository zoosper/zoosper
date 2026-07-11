<?php

declare(strict_types=1);

/**
 * Demonstrate a Zoosper helpful error in CLI format.
 */

$basePath = require __DIR__ . '/bootstrap.php';

try {
    (new \Zoosper\Core\Container\ServiceContainer())->get('Acme\\Missing\\Service');
} catch (Throwable $exception) {
    print (new \Zoosper\Core\Exception\ConsoleExceptionFormatter())->format($exception);
}
