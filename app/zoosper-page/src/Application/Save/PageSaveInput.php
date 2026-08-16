<?php

declare(strict_types=1);

namespace Zoosper\Page\Application\Save;

use RuntimeException;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Html\HtmlSanitizerInterface;
use Zoosper\Page\Content\BlockJsonValidator;

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
        ?HtmlSanitizerInterface $sanitizer = null,
        ?ConfigRepository $config = null,
    ): self {
        $slug = strtolower(trim((string) ($form['slug'] ?? '')));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?: '';

        return new self(
            siteId: (int) ($form['site_id'] ?? 0),
            title: trim((string) ($form['title'] ?? '')),
            slug: trim($slug, '-'),
            content: $sanitizer?->sanitise((string) ($form['content'] ?? ''))->toString()
                ?? (string) ($form['content'] ?? ''),
            contentFormat: in_array((string) ($form['content_format'] ?? 'html'), ['html', 'block_json'], true) ? (string) ($form['content_format'] ?? 'html') : 'html',
            publish: isset($form['publish']),
            contentJson: self::normaliseContentJson($form['content_json'] ?? null, $config),
            metaTitle: self::optional($form['meta_title'] ?? null),
            metaDescription: self::optional($form['meta_description'] ?? null),
            metaKeywords: self::optional($form['meta_keywords'] ?? null),
            canonicalUrl: self::optional($form['canonical_url'] ?? null),
        );
    }

    private static function normaliseContentJson(mixed $value, ?ConfigRepository $config): ?string
    {
        $json = trim((string) ($value ?? ''));
        if ($json === '') {
            return null;
        }
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid Editor.js JSON payload.');
        }
        $contentModel = $config?->array('content_model') ?? [];
        $result = (new BlockJsonValidator($contentModel['block_json'] ?? []))->validate($decoded);
        if (!$result->valid) {
            throw new RuntimeException('Invalid Editor.js JSON payload: ' . implode(' ', $result->errors));
        }

        return json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: null;
    }

    private static function optional(mixed $value): ?string
    {
        $normalised = trim((string) ($value ?? ''));
        return $normalised === '' ? null : $normalised;
    }
}
