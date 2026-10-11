<?php

declare(strict_types=1);

namespace Zoosper\Mail\Transport;

use RuntimeException;
use Zoosper\Mail\Config\SmtpConfig;
use Zoosper\Mail\Message\EmailAddress;
use Zoosper\Mail\Message\EmailMessage;

/**
 * Minimal dependency-light SMTP transport for Zoosper system emails.
 *
 * Supports plain SMTP, SSL and STARTTLS. This class must never log SMTP
 * passwords, message bodies, OTPs, recovery codes, reset tokens or QR data.
 */
final class SmtpMailer implements MailerInterface
{
    /** @var resource|null */
    private $socket = null;

    public function __construct(private readonly SmtpConfig $config)
    {
    }

    #[\Override]
    public function send(EmailMessage $message): void
    {
        $this->connect();

        try {
            $this->expect([220]);
            $this->command('EHLO localhost', [250]);

            if ($this->config->encryption() === 'tls') {
                $this->command('STARTTLS', [220]);
                $socket = $this->connectedSocket();
                if (stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) !== true) {
                    throw new RuntimeException('Unable to enable SMTP TLS.');
                }
                $this->command('EHLO localhost', [250]);
            }

            if ($this->config->username() !== '') {
                $this->command('AUTH LOGIN', [334]);
                $this->command(base64_encode($this->config->username()), [334]);
                $this->command(base64_encode($this->config->password()), [235]);
            }

            $this->command('MAIL FROM:<' . $message->from->email . '>', [250]);
            foreach ($message->to as $recipient) {
                $this->command('RCPT TO:<' . $recipient->email . '>', [250, 251]);
            }

            $this->command('DATA', [354]);
            $this->write($this->buildPayload($message) . "\r\n.");
            $this->expect([250]);
            $this->command('QUIT', [221]);
        } finally {
            $this->disconnect();
        }
    }

    /**
     * Open the SMTP socket.
     */
    private function connect(): void
    {
        $scheme = $this->config->encryption() === 'ssl' ? 'ssl://' : '';
        $target = $scheme . $this->config->host() . ':' . $this->config->port();
        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client($target, $errno, $errstr, $this->config->timeoutSeconds());
        if (!is_resource($socket)) {
            throw new RuntimeException('Unable to connect to SMTP server.');
        }

        stream_set_timeout($socket, $this->config->timeoutSeconds());
        $this->socket = $socket;
    }

    /**
     * Close the SMTP socket.
     */
    private function disconnect(): void
    {
        $socket = $this->socket;
        $this->socket = null;

        if (is_resource($socket)) {
            fclose($socket);
        }
    }

    /**
     * @param list<int> $expectedCodes
     */
    private function command(string $command, array $expectedCodes): void
    {
        $this->write($command);
        $this->expect($expectedCodes);
    }

    private function write(string $line): void
    {
        $socket = $this->connectedSocket();
        $payload = $line . "\r\n";
        $written = fwrite($socket, $payload);
        if ($written === false || $written !== strlen($payload)) {
            throw new RuntimeException('Unable to write complete SMTP command.');
        }
    }

    /**
     * @param list<int> $expectedCodes
     */
    private function expect(array $expectedCodes): void
    {
        $socket = $this->connectedSocket();

        do {
            $line = fgets($socket);
            if ($line === false || $line === '') {
                throw new RuntimeException('Empty SMTP response.');
            }
        } while (isset($line[3]) && $line[3] === '-');

        $code = (int) substr($line, 0, 3);
        if (!in_array($code, $expectedCodes, true)) {
            throw new RuntimeException('Unexpected SMTP response code: ' . $code);
        }
    }

    /** @return resource */
    private function connectedSocket()
    {
        if (!is_resource($this->socket)) {
            throw new RuntimeException('SMTP socket is not connected.');
        }

        return $this->socket;
    }

    private function buildPayload(EmailMessage $message): string    {
        $headers = [
            'From' => $message->from->headerValue(),
            'To' => implode(', ', array_map(static fn (EmailAddress $address): string => $address->headerValue(), $message->to)),
            'Subject' => $this->headerValue($message->subject),
            'MIME-Version' => '1.0',
        ];

        foreach ($message->headers as $name => $value) {
            $headers[$this->headerValue($name)] = $this->headerValue($value);
        }

        if ($message->htmlBody !== null) {
            $boundary = 'zoosper-' . bin2hex(random_bytes(12));
            $headers['Content-Type'] = 'multipart/alternative; boundary="' . $boundary . '"';
            $body = '--' . $boundary . "\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\n\r\n"
                . $this->normaliseBody($message->textBody) . "\r\n"
                . '--' . $boundary . "\r\n"
                . "Content-Type: text/html; charset=UTF-8\r\n\r\n"
                . $this->normaliseBody($message->htmlBody) . "\r\n"
                . '--' . $boundary . '--';
        } else {
            $headers['Content-Type'] = 'text/plain; charset=UTF-8';
            $body = $this->normaliseBody($message->textBody);
        }

        $headerLines = [];
        foreach ($headers as $name => $value) {
            $headerLines[] = $name . ': ' . $value;
        }

        return implode("\r\n", $headerLines) . "\r\n\r\n" . $body;
    }

    private function headerValue(string $value): string
    {
        return str_replace(["\r", "\n"], '', $value);
    }

    private function normaliseBody(string $body): string
    {
        $body = str_replace(["\r\n", "\r"], "\n", $body);
        $lines = array_map(
            static fn (string $line): string => str_starts_with($line, '.') ? '.' . $line : $line,
            explode("\n", $body),
        );

        return implode("\r\n", $lines);
    }
}










