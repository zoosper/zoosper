<?php

declare(strict_types=1);

use Zoosper\Mail\Log\EmailLogRepository;
use Zoosper\Mail\Message\EmailAddress;
use Zoosper\Mail\Message\EmailMessage;
use Zoosper\Mail\Transport\LoggedMailer;
use Zoosper\Mail\Transport\MailerInterface;

it('does not record a delivered message as failed when success logging fails', function (): void {
    $pdo = new class('sqlite::memory:') extends PDO {
        public int $calls = 0;

        public function prepare(string $query, array $options = []): PDOStatement|false
        {
            ++$this->calls;
            throw new RuntimeException('Log unavailable');
        }
    };
    $inner = new class implements MailerInterface {
        public function send(EmailMessage $message): void {}
    };
    $message = new EmailMessage(new EmailAddress('from@example.test'), [new EmailAddress('to@example.test')], 'Subject', 'Body');

    expect(fn () => (new LoggedMailer($inner, new EmailLogRepository($pdo)))->send($message))
        ->toThrow(RuntimeException::class, 'Log unavailable');
    expect($pdo->calls)->toBe(1);
});

it('preserves the transport error when failure logging also fails', function (): void {
    $pdo = new class('sqlite::memory:') extends PDO {
        public function prepare(string $query, array $options = []): PDOStatement|false
        {
            throw new RuntimeException('Log unavailable');
        }
    };
    $inner = new class implements MailerInterface {
        public function send(EmailMessage $message): void
        {
            throw new RuntimeException('Delivery failed');
        }
    };
    $message = new EmailMessage(new EmailAddress('from@example.test'), [new EmailAddress('to@example.test')], 'Subject', 'Body');

    expect(fn () => (new LoggedMailer($inner, new EmailLogRepository($pdo)))->send($message))
        ->toThrow(RuntimeException::class, 'Delivery failed');
});