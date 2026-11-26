<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Mail\Config\SmtpConfig;
use Zoosper\Mail\Message\EmailAddress;
use Zoosper\Mail\Message\EmailMessage;
use Zoosper\Mail\Transport\SmtpMailer;

function sendThroughTestSmtpServer(string $dataResponse, ?string $quitResponse): void
{
    if (!function_exists('pcntl_fork')) {
        test()->markTestSkipped('The loopback fixture requires pcntl.');
    }
    $server = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
    expect($server)->not->toBeFalse();
    $address = stream_socket_get_name($server, false);
    $port = (int) substr(strrchr($address, ':'), 1);
    $pid = pcntl_fork();
    if ($pid === 0) {
        $client = stream_socket_accept($server, 5);
        if ($client === false) {
            exit(1);
        }
        stream_set_timeout($client, 5);
        fwrite($client, "220 ready\r\n");
        foreach (["250 hello\r\n", "250 sender\r\n", "250 recipient\r\n", "354 send data\r\n"] as $response) {
            if (fgets($client) === false) {
                exit(1);
            }
            fwrite($client, $response);
        }
        while (($line = fgets($client)) !== false && $line !== ".\r\n") {
        }
        if ($line === false) {
            exit(1);
        }
        fwrite($client, $dataResponse . "\r\n");
        if ($quitResponse !== null && fgets($client) !== false) {
            fwrite($client, $quitResponse . "\r\n");
        }
        fclose($client);
        fclose($server);
        exit(0);
    }
    expect($pid)->toBeGreaterThan(0);
    fclose($server);

    $config = new SmtpConfig(ConfigRepository::fromArray(['mail' => ['smtp' => [
        'host' => '127.0.0.1', 'port' => $port, 'timeout_seconds' => 2,
    ]]]));
    $mailer = new SmtpMailer($config);
    $message = new EmailMessage(new EmailAddress('from@example.test'), [new EmailAddress('to@example.test')], 'Subject', 'Body');

    try {
        $mailer->send($message);
    } finally {
        pcntl_waitpid($pid, $status);
        expect(pcntl_wexitstatus($status))->toBe(0);
    }
}

it('treats accepted DATA as sent when the server closes before QUIT', function (): void {
    sendThroughTestSmtpServer('250 accepted', null);
});

it('treats accepted DATA as sent when QUIT is rejected', function (): void {
    sendThroughTestSmtpServer('250 accepted', '500 error');
});

it('reports DATA rejection as failure', function (): void {
    expect(fn () => sendThroughTestSmtpServer('550 rejected', null))
        ->toThrow(RuntimeException::class, 'Unexpected SMTP response code: 550');
});

it('rejects unknown encryption before connecting', function (): void {
    if (!function_exists('pcntl_fork')) {
        test()->markTestSkipped('The loopback fixture requires pcntl.');
    }

    $server = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
    expect($server)->not->toBeFalse();
    $address = stream_socket_get_name($server, false);
    $port = (int) substr(strrchr($address, ':'), 1);
    $pid = pcntl_fork();
    if ($pid === 0) {
        $client = @stream_socket_accept($server, 1);
        if ($client !== false) {
            fwrite($client, "220 ready\r\n");
            fgets($client);
            fwrite($client, "250 hello\r\n");
            fgets($client);
            fwrite($client, "334 username\r\n");
            fgets($client);
            fwrite($client, "334 password\r\n");
            $password = fgets($client);
            fclose($client);
            exit($password === base64_encode('secret') . "\r\n" ? 1 : 2);
        }
        fclose($server);
        exit(0);
    }
    expect($pid)->toBeGreaterThan(0);
    fclose($server);
    $config = new SmtpConfig(ConfigRepository::fromArray(['mail' => ['smtp' => [
        'host' => '127.0.0.1', 'port' => $port, 'encryption' => 'tlss',
        'username' => 'user', 'password' => 'secret',
    ]]]));
    $message = new EmailMessage(new EmailAddress('from@example.test'), [new EmailAddress('to@example.test')], 'Subject', 'Body');

    try {
        expect(fn () => (new SmtpMailer($config))->send($message))
            ->toThrow(RuntimeException::class, 'Unsupported SMTP encryption.');
    } finally {
        pcntl_waitpid($pid, $status);
        expect(pcntl_wexitstatus($status))->toBe(0);
    }
});