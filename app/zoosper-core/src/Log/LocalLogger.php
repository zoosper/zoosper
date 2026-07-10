<?php

declare(strict_types=1);

namespace Zoosper\Core\Log;

use Throwable;

final readonly class LocalLogger
{
    public function __construct(private string $file)
    {
    }

    /** @param array<string, mixed> $context */
    public function debug(string $message, array $context = []): void { $this->write('DEBUG', $message, $context); }
    /** @param array<string, mixed> $context */
    public function info(string $message, array $context = []): void { $this->write('INFO', $message, $context); }
    /** @param array<string, mixed> $context */
    public function warning(string $message, array $context = []): void { $this->write('WARNING', $message, $context); }
    /** @param array<string, mixed> $context */
    public function error(string $message, array $context = []): void { $this->write('ERROR', $message, $context); }
    /** @param array<string, mixed> $context */
    public function critical(string $message, array $context = []): void { $this->write('CRITICAL', $message, $context); }

    /** @param array<string, mixed> $context */
    public function exception(Throwable $exception, array $context = []): void
    {
        $context['exception'] = [
            'class' => $exception::class,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];
        $this->error($exception->getMessage(), $context);
    }

    /** @param array<string, mixed> $context */
    private function write(string $level, string $message, array $context): void
    {
        $directory = dirname($this->file);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $line = sprintf(
            "[%s] %s: %s %s\n",
            gmdate('Y-m-d H:i:s'),
            $level,
            $message,
            $context !== [] ? json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : ''
        );

        file_put_contents($this->file, $line, FILE_APPEND | LOCK_EX);
    }
}
