<?php
declare(strict_types=1);

namespace Zoosper\Page\Content;

use Zoosper\Core\Html\HtmlSanitizerInterface;
use Zoosper\Page\Model\Page;
/** Server-authoritative Page document rendering and safe HTML fallback boundary. */
final readonly class DocumentRenderer
{
    public function __construct(
        private DocumentNormalizer $normalizer,
        private BlockJsonToHtmlRenderer $blocks,
        private HtmlSanitizerInterface $sanitizer,
    ) {}
    public function renderPage(Page $page): string
    {
        if (!$page->hasBlockJson()) {
            return $page->content;
        }
        $document = $this->normalizer->tolerant($page->contentJson);
        if ($document === null) {
            return $page->content;
        }
        $html = $this->blocks->render($document);
        return trim($html) === '' ? $page->content : $this->sanitizer->sanitise($html)->toString();
    }
    /** @param array<string,mixed> $document */
    public function renderDocument(array $document): string
    {
        return $this->sanitizer->sanitise($this->blocks->render($this->normalizer->fromArray($document)))->toString();
    }
}
