<?php

declare(strict_types=1);

namespace Zoosper\Admin\Editor;

/**
 * Renders an admin content editor for CMS body fields.
 *
 * Implementations must keep posted field names stable so server-side sanitising
 * continues to protect saved content. Editors must never store OTPs, TOTP
 * secrets, recovery-code plaintext, reset tokens, SMTP passwords, payment data,
 * session IDs, CSRF tokens or customer-private values.
 */
interface ContentEditorInterface
{
    /** Unique editor code used by config/editor.php. */
    public function code(): string;

    /**
     * Render the editor field HTML.
     *
     * @param array<string, mixed> $context
     */
    public function render(string $fieldName, string $value, array $context = []): string;
}
