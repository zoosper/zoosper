<?php

declare(strict_types=1);

namespace Zoosper\Core\Console;

/**
 * Small output helper for Zoosper CLI commands.
 *
 * It intentionally avoids storing output internally so long-running operational
 * commands can stream progress safely. Commands must never write secrets, OTPs,
 * TOTP secrets, recovery-code plaintext, reset tokens, SMTP passwords or payment
 * data to this output.
 */
final readonly class ConsoleOutput
{
    /** @param resource|null $stdout @param resource|null $stderr */
    public function __construct(private mixed $stdout = null, private mixed $stderr = null)
    {
    }

    public function write(string $message): void
    {
        fwrite($this->stdout ?? STDOUT, $message);
    }

    public function writeln(string $message = ''): void
    {
        $this->write($message . PHP_EOL);
    }

    public function error(string $message): void
    {
        fwrite($this->stderr ?? STDERR, $message);
    }

    public function errorln(string $message = ''): void
    {
        $this->error($message . PHP_EOL);
    }
}
