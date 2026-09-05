<?php

declare(strict_types=1);

namespace Zoosper\Page\Application\Save;

use RuntimeException;

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Html\HtmlSanitizerInterface;
use Zoosper\Page\Content\DocumentNormalizer;
use Zoosper\Page\Content\DocumentValidator;

/** Immutable, normalised input shared by Page create and update operations. */
final readonly class PageSaveInput
{
    public function __construct(
        public int $siteId,
        public string $title,
        public string $slug,
        public string $content,
        public string $contentFormat,
        public bool $publish,
        public ?string $contentJson,
        public ?string $metaTitle,
        public ?string $metaDescription,
        public ?string $metaKeywords,
        public ?string $canonicalUrl,
    ) {
    }

    /** @param array<string, mixed> $form */
    public static function fromForm(
        array $form,
        HtmlSanitizerInterface $sanitizer,
        ?ConfigRepository $config = null,
        ?DocumentNormalizer $documents = null,
    ): self {
        $slug = strtolower(trim((string) ($form['slug'] ?? '')));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?: '';

        $rawContent = (string) ($form['content'] ?? '');
        $sanitized = $sanitizer->sanitise($rawContent);

        return new self(
            siteId: (int) ($form['site_id'] ?? 0),
            title: trim((string) ($form['title'] ?? '')),
            slug: trim($slug, '-'),
            content: $sanitized->toString(),
            contentFormat: in_array((string) ($form['content_format'] ?? 'html'), ['html', 'block_json'], true) ? (string) ($form['content_format'] ?? 'html') : 'html',
            publish: isset($form['publish']),
            contentJson: self::normaliseContentJson($form['content_json'] ?? null, $documents, $config),
            metaTitle: self::optional($form['meta_title'] ?? null),
            metaDescription: self::optional($form['meta_description'] ?? null),
            metaKeywords: self::optional($form['meta_keywords'] ?? null),
            canonicalUrl: self::optional($form['canonical_url'] ?? null),
        );
    }

    private static function normaliseContentJson(
        mixed $value,
        ?DocumentNormalizer $documents,
        ?ConfigRepository $config,
    ): ?string
    {
        $json = trim((string) ($value ?? ''));
        if ($json === '') {
            return null;
        }
        if ($documents === null) {
            $documents = new DocumentNormalizer(new DocumentValidator($config));
        }

        return $documents->encode($documents->fromJson($json));
    }
    private static function optional(mixed $value): ?string
    {
        $normalised = trim((string) ($value ?? ''));
        return $normalised === '' ? null : $normalised;
    }
}










