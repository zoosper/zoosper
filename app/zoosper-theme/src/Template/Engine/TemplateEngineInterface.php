<?php

declare(strict_types=1);

namespace Zoosper\Theme\Template\Engine;

/**
 * Contract for template engines used by Zoosper themes and module views.
 *
 * Implementations may render PHP templates, Latte templates, Twig templates or
 * custom engine formats. Template engines must not expose secrets, session IDs,
 * CSRF tokens, OTPs, TOTP secrets, recovery-code plaintext, reset tokens, SMTP
 * passwords, payment data or customer-private values in public output.
 */
interface TemplateEngineInterface
{
    /**
     * Return the lowercase file extensions handled by this engine.
     *
     * @return list<string>
     */
    public function extensions(): array;

    /**
     * Render a template file with the provided view data.
     *
     * @param array<string, mixed> $data
     */
    public function renderFile(string $path, array $data): string;
}
