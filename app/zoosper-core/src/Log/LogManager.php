<?php

declare(strict_types=1);

namespace Zoosper\Core\Log;

use Zoosper\Core\Config\ConfigRepository;

final class LogManager
{
    /** @var array<string, LocalLogger> */
    private array $loggers = [];

    public function __construct(private ConfigRepository $config, private string $basePath)
    {
    }

    public function default(): LocalLogger
    {
        return $this->forFile((string) ($this->config->get('logging.default_file', 'system.log') ?? 'system.log'));
    }

    public function exceptions(): LocalLogger
    {
        return $this->forFile((string) ($this->config->get('logging.error_file', 'exception.log') ?? 'exception.log'));
    }

    public function module(string $moduleName): LocalLogger
    {
        $configured = $this->config->get('logging.modules.' . $moduleName, null);
        $file = is_string($configured) && $configured !== '' ? $configured : $moduleName . '.log';

        return $this->forFile($file);
    }

    public function forFile(string $file): LocalLogger
    {
        $file = ltrim($file, '/');
        $path = (string) ($this->config->get('logging.path', 'var/log') ?? 'var/log');
        $enabled = (bool) ($this->config->get('logging.enabled', true) ?? true);
        $fullPath = $this->basePath . '/' . trim($path, '/') . '/' . $file;

        return $this->loggers[$fullPath] ??= new LocalLogger($fullPath, $enabled);
    }
}
