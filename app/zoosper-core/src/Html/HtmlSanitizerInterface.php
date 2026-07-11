<?php

declare(strict_types=1);

namespace Zoosper\Core\Html;

/**
 * Sanitises rich CMS HTML before it is rendered by frontend templates.
 *
 * Implementations must be suitable for user/editor generated content and must
 * never log or expose sensitive values such as OTPs, TOTP secrets,
 * recovery-code plaintext, reset tokens, SMTP passwords, payment data or
 * customer-private information.
 */
interface HtmlSanitizerInterface
{
    /**
     * Sanitise untrusted or editor-generated HTML and return a safe value object.
     */
    public function sanitise(string $html): SanitizedHtml;
}
