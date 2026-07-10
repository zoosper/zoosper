<?php

declare(strict_types=1);

namespace Zoosper\Mail\Diagnostics;

use Zoosper\Mail\Config\SmtpConfig;

/**
 * Performs a safe SMTP connectivity probe.
 *
 * The probe only tests whether a socket can be opened to the configured SMTP
 * endpoint. It does not authenticate and must never expose SMTP passwords,
 * message bodies, reset tokens, OTPs or recovery codes.
 */
final readonly class SmtpConnectionProbe
{
    public function __construct(private SmtpConfig $config)
    {
    }

    /**
     * Return true when the SMTP endpoint is reachable.
     */
    public function canConnect(): bool
    {
        $scheme = $this->config->encryption() === 'ssl' ? 'ssl://' : '';
        $target = $scheme . $this->config->host() . ':' . $this->config->port();
        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client($target, $errno, $errstr, $this->config->timeoutSeconds());

        if (!is_resource($socket)) {
            return false;
        }

        fclose($socket);
        return true;
    }
}
