<?php

declare(strict_types=1);

namespace Zoosper\Mail\Log;

use PDO;
use Throwable;
use Zoosper\Mail\Message\EmailAddress;
use Zoosper\Mail\Message\EmailMessage;

/**
 * Persists outbound SMTP mail attempts for operational visibility.
 *
 * The repository can store email subject/body content because the admin mail-log
 * grid is intended to show sent email content for diagnostics. Callers must not
 * send OTP values, TOTP setup secrets, provisioning URIs, recovery-code
 * plaintext, reset tokens, SMTP passwords or payment data through messages that
 * will be stored here unless a later masking policy explicitly protects them.
 */
final readonly class EmailLogRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Record a successful outbound mail send.
     */
    public function recordSuccess(string $messageUuid, EmailMessage $message, string $transport = 'smtp'): void
    {
        $this->insert($messageUuid, $message, 'sent', $transport, null, null);
    }

    /**
     * Record a failed outbound mail send with non-sensitive error metadata.
     */
    public function recordFailure(string $messageUuid, EmailMessage $message, Throwable $exception, string $transport = 'smtp'): void
    {
        $this->insert($messageUuid, $message, 'failed', $transport, $exception::class, $exception->getMessage());
    }

    /**
     * Search the mail log using safe, parameterised filters.
     *
     * @param array{status?:string,email?:string,subject?:string} $filters
     * @return list<array<string, mixed>>
     */
    public function search(array $filters = [], int $limit = 100): array
    {
        $where = [];
        $params = [];

        if (($filters['status'] ?? '') !== '') {
            $where[] = 'status = :status';
            $params['status'] = $filters['status'];
        }

        if (($filters['email'] ?? '') !== '') {
            $where[] = '(from_email LIKE :email OR to_emails LIKE :email)';
            $params['email'] = '%' . $filters['email'] . '%';
        }

        if (($filters['subject'] ?? '') !== '') {
            $where[] = 'subject LIKE :subject';
            $params['subject'] = '%' . $filters['subject'] . '%';
        }

        $sql = 'SELECT * FROM smtp_email_log';
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY id DESC LIMIT ' . max(1, min(500, $limit));

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Find one email log row by ID.
     *
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM smtp_email_log WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    private function insert(string $messageUuid, EmailMessage $message, string $status, string $transport, ?string $errorClass, ?string $errorMessage): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO smtp_email_log (message_uuid, transport, status, from_email, from_name, to_emails, subject, text_body, html_body, error_class, error_message, created_at, sent_at, failed_at)
             VALUES (:message_uuid, :transport, :status, :from_email, :from_name, :to_emails, :subject, :text_body, :html_body, :error_class, :error_message, NOW(), :sent_at, :failed_at)'
        );

        $statement->execute([
            'message_uuid' => $messageUuid,
            'transport' => $transport,
            'status' => $status,
            'from_email' => $message->from->email,
            'from_name' => $message->from->name,
            'to_emails' => implode(', ', array_map(static fn (EmailAddress $address): string => $address->email, $message->to)),
            'subject' => mb_substr($message->subject, 0, 255),
            'text_body' => $message->textBody,
            'html_body' => $message->htmlBody,
            'error_class' => $errorClass,
            'error_message' => $errorMessage,
            'sent_at' => $status === 'sent' ? date('Y-m-d H:i:s') : null,
            'failed_at' => $status === 'failed' ? date('Y-m-d H:i:s') : null,
        ]);
    }
}
