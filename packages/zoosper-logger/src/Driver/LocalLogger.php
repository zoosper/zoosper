<?php
declare(strict_types=1);
namespace Zoosper\Logger\Driver;
use Marko\Log\Contracts\LoggerInterface as MarkoLoggerInterface;
use Throwable;
use Zoosper\Logger\Contract\LoggerInterface;
final class LocalLogger implements LoggerInterface
{
    private MarkoLoggerInterface $driver;
    private string $legacyFile;
    private string $rotatedFile;
    private bool $enabled;
    public function __construct(MarkoLoggerInterface|string $driver, string|bool $legacyFile = '', string $rotatedFile = '', bool $enabled = true)
    {
        if ($driver instanceof MarkoLoggerInterface) {
            $this->driver = $driver;
            $this->legacyFile = (string) $legacyFile;
            $this->rotatedFile = $rotatedFile;
            $this->enabled = $enabled;
            return;
        }
        $this->legacyFile = $driver;
        $this->enabled = is_bool($legacyFile) ? $legacyFile : $enabled;
        $directory = dirname($driver);
        $channel = pathinfo(basename($driver), PATHINFO_FILENAME) ?: 'app';
        $rotation = new \Marko\Log\File\Rotation\DailyRotation();
        $this->rotatedFile = $rotation->getCurrentPath($directory, $channel);
        $this->driver = new \Marko\Log\File\Driver\FileLogger(
            $directory,
            $channel,
            \Marko\Log\LogLevel::Debug,
            new \Marko\Log\Formatter\LineFormatter('[{datetime}] {channel}.{level}: {message} {context}', 'Y-m-d H:i:s', true),
            $rotation,
        );
    }
    public function emergency(string $message, array $context = []): void { $this->write('emergency', $message, $context); }
    public function alert(string $message, array $context = []): void { $this->write('alert', $message, $context); }
    public function critical(string $message, array $context = []): void { $this->write('critical', $message, $context); }
    public function error(string $message, array $context = []): void { $this->write('error', $message, $context); }
    public function warning(string $message, array $context = []): void { $this->write('warning', $message, $context); }
    public function notice(string $message, array $context = []): void { $this->write('notice', $message, $context); }
    public function info(string $message, array $context = []): void { $this->write('info', $message, $context); }
    public function debug(string $message, array $context = []): void { $this->write('debug', $message, $context); }
    public function exception(Throwable $exception, array $context = []): void
    {
        $context['exception'] = ['class' => $exception::class, 'message' => $exception->getMessage(), 'file' => $exception->getFile(), 'line' => $exception->getLine(), 'trace' => $exception->getTraceAsString()];
        $this->error($exception->getMessage(), $context);
    }
    private function write(string $method, string $message, array $context): void
    {
        if (!$this->enabled) return;
        $this->driver->{$method}($message, $this->redact($context));
        $this->refreshLegacyLink();
    }
    private function refreshLegacyLink(): void
    {
        if (!is_file($this->rotatedFile)) return;
        $directory = dirname($this->legacyFile);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) return;
        $target = basename($this->rotatedFile);
        if (is_file($this->legacyFile) && !is_link($this->legacyFile)) return;
        if (is_link($this->legacyFile) && readlink($this->legacyFile) === $target) return;
        if (is_link($this->legacyFile)) @unlink($this->legacyFile);
        if (!@symlink($target, $this->legacyFile) && !is_file($this->legacyFile)) {
            @copy($this->rotatedFile, $this->legacyFile);
        }
    }
    private function redact(array $context): array
    {
        foreach ($context as $key => $value) {
            $normalised = strtolower((string) $key);
            if (preg_match('/password|token|secret|session|authorization|bearer|cookie|api[_-]?key|credential/i', $normalised) === 1) { $context[$key] = '[redacted]'; continue; }
            if (is_string($value) && preg_match('/(?:Bearer\s+)?zp_pat_[a-f0-9]{16}_[a-f0-9]{64}/i', $value) === 1) { $context[$key] = preg_replace('/(?:Bearer\s+)?zp_pat_[a-f0-9]{16}_[a-f0-9]{64}/i', '[redacted]', $value); continue; }
            if (is_array($value)) $context[$key] = $this->redact($value);
        }
        return $context;
    }
}
