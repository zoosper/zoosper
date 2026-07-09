<?php
declare(strict_types=1);

use Zoosper\Core\Bootstrap\ApplicationFactory;

require dirname(__DIR__) . '/bootstrap/autoload.php';
ApplicationFactory::create(dirname(__DIR__))->handle();
