<?php

declare(strict_types=1);

namespace Zoosper\Page\Sanitization;

/**
 * Immutable-style report object for CMS page content sanitisation audits.
 *
 * The report intentionally stores metadata only: row IDs, lengths, hashes and
 * detected pattern names. It must not contain full CMS body HTML, OTPs, TOTP
 * secrets, recovery-code plaintext, reset tokens, SMTP passwords, payment data,
 * session IDs or customer-private values.
 */
final class PageContentSanitizationResult
{
    /** @var list<array<string, mixed>> */
    private array $changedRows = [];

    public function __construct(
        public readonly string $table,
        public readonly bool $tableExists,
        public int $scanned = 0,
        public int $changed = 0,
        public int $repaired = 0,
    ) {
    }

    /**
     * Record metadata for a row that would change after sanitisation.
     *
     * @param list<string> $patterns
     */
    public function addChangedRow(int $id, int $beforeLength, int $afterLength, string $beforeHash, string $afterHash, array $patterns): void
    {
        $this->changed++;
        $this->changedRows[] = [
            'id' => $id,
            'before_length' => $beforeLength,
            'after_length' => $afterLength,
            'before_hash' => $beforeHash,
            'after_hash' => $afterHash,
            'patterns' => $patterns,
        ];
    }

    /**
     * Mark a changed row as repaired by the repair tool.
     */
    public function markRepaired(): void
    {
        $this->repaired++;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function changedRows(): array
    {
        return $this->changedRows;
    }
}
