<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Database\ConnectionFactory;
use Zoosper\Core\Html\HtmlSanitizerFactory;
use Zoosper\Page\Sanitization\PageContentSanitizationAuditor;

$basePath = require __DIR__ . '/bootstrap.php';
$config = ConfigRepository::fromPath($basePath . '/config');
$pdo = (new ConnectionFactory($config, $basePath))->create();

/** @var array<string, mixed> $htmlConfig */
$htmlConfig = $config->array('html_sanitizer');
$htmlConfig['cache_path'] = $basePath . '/' . ltrim((string) ($htmlConfig['cache_path'] ?? 'var/cache/htmlpurifier'), '/');

return new PageContentSanitizationAuditor(
    $pdo,
    (new HtmlSanitizerFactory($htmlConfig))->create(),
);
