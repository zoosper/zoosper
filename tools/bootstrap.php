<?php

declare(strict_types=1);

/**
 * Shared bootstrap for Zoosper repository tools.
 *
 * The application bootstrap owns Composer loading, `.env` parsing and the
 * global env() helper. Tools intentionally do not maintain a second parser or
 * environment lookup implementation.
 */

$basePath = dirname(__DIR__);

require_once $basePath . '/bootstrap/autoload.php';

return $basePath;



