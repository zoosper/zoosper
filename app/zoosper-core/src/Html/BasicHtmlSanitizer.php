<?php

declare(strict_types=1);

namespace Zoosper\Core\Html;

/**
 * Conservative fallback HTML sanitizer used only when HTML Purifier is absent.
 *
 * This fallback removes script/style/iframe/object/embed/link/meta tags and
 * strips inline event-handler attributes. It is intentionally conservative and
 * should not be treated as equivalent to a full HTML parser. Production WYSIWYG
 * content should use HtmlPurifierSanitizer.
 */
final readonly class BasicHtmlSanitizer implements HtmlSanitizerInterface
{
    public function sanitise(string $html): SanitizedHtml
    {
        $clean = preg_replace('#<(script|style|iframe|object|embed|link|meta)\b[^>]*>.*?</\1>#is', '', $html) ?? '';
        $clean = preg_replace('#<(script|style|iframe|object|embed|link|meta)\b[^>]*\/?\s*>#is', '', $clean) ?? '';
        $clean = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? '';
        $clean = preg_replace('/\s(href|src)\s*=\s*("|\')\s*javascript:[^"\']*\2/i', ' $1="#"', $clean) ?? '';

        return new SanitizedHtml($clean);
    }
}
