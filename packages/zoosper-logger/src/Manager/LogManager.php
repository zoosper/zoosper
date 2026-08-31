<?php
declare(strict_types=1);
namespace Zoosper\Logger\Manager;
use Marko\Log\File\Driver\FileLogger;
use Marko\Log\File\Rotation\DailyRotation;
use Marko\Log\Formatter\LineFormatter;
use Marko\Log\LogLevel;
use Zoosper\Logger\Contract\LoggerInterface;
use Zoosper\Logger\Driver\LocalLogger;
final class LogManager
{
    private array $loggers = [];
    public function __construct(private object $config, private string $basePath) {}
    public function default(): LoggerInterface { return $this->forFile((string) ($this->value('logging.default_file', 'system.log') ?? 'system.log')); }
    public function exceptions(): LoggerInterface { return $this->forFile((string) ($this->value('logging.error_file', 'exception.log') ?? 'exception.log')); }
    public function module(string $moduleName): LoggerInterface
    {
        $configured = $this->value('logging.modules.' . $moduleName, null);
        return $this->forFile(is_string($configured) && $configured !== '' ? $configured : $moduleName . '.log');
    }
    public function forFile(string $file): LoggerInterface
    {
        $legacyName = ltrim($file, '/');
        $path = (string) ($this->value('logging.path', 'var/log') ?? 'var/log');
        $directory = $this->basePath . '/' . trim($path, '/');
        $channel = pathinfo(basename($legacyName), PATHINFO_FILENAME);
        if ($channel === '') $channel = 'app';
        $key = $directory . '/' . $legacyName;
        if (isset($this->loggers[$key])) return $this->loggers[$key];
        $format = (string) ($this->value('logging.format', '[{datetime}] {channel}.{level}: {message} {context}') ?? '[{datetime}] {channel}.{level}: {message} {context}');
        $dateFormat = (string) ($this->value('logging.date_format', 'Y-m-d H:i:s') ?? 'Y-m-d H:i:s');
        $escape = (bool) ($this->value('logging.escape_newlines', true) ?? true);
        $level = LogLevel::tryFrom(strtolower((string) ($this->value('logging.level', 'debug') ?? 'debug'))) ?? LogLevel::Debug;
        $rotation = new DailyRotation();
        $driver = new FileLogger($directory, $channel, $level, new LineFormatter($format, $dateFormat, $escape), $rotation);
        return $this->loggers[$key] = new LocalLogger($driver, $directory . '/' . $legacyName, $rotation->getCurrentPath($directory, $channel), (bool) ($this->value('logging.enabled', true) ?? true));
    }
    private function value(string $key, mixed $default): mixed { return $this->config->get($key, $default); }
}











