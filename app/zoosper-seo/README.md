# zoosper/seo

Extensible SEO metadata, sitemap, and robots orchestration for Zoosper CMS. The module owns generic SEO contracts and output while feature modules retain responsibility for discovering and describing their own public resources.

## Responsibilities

- Define engine-neutral metadata and sitemap contribution contracts.
- Discover module-owned contributor declarations from `config/seo.php`.
- Aggregate, validate, deduplicate, sort, and XML-escape sitemap entries.
- Own the public `/sitemap.xml` and `/robots.txt` routes and response formats.
- Keep generic SEO orchestration independent of concrete Page, Blog, Catalogue, or other feature models and repositories.

## Architecture

`SeoContributorRegistry` scans enabled modules for `config/seo.php`. Declared service IDs are resolved from the application service container and validated against `SeoMetadataContributorInterface` or `SitemapContributorInterface`. `SeoMetadataManager` selects the first contributor that supports a rendered resource. `SitemapAggregator` combines entries from every sitemap contributor and emits deterministic XML.

Feature modules own resource eligibility and database queries. For example, `zoosper-page` contributes Page metadata and published Page URLs without the SEO module importing Page classes.

## Configuration

A feature module contributes services through `config/seo.php`:

```php
return [
    'metadata' => [PageSeoContributor::class],
    'sitemap' => [PageSitemapContributor::class],
];
```

Every declared class must also be registered as a service by the contributing module.

## Extension points

- `SeoMetadataContributorInterface` contributes normalised metadata for a supported resource.
- `SitemapContributorInterface` yields public `SitemapEntry` values for one Site.
- Additional modules can contribute resources without modifying `zoosper-seo` or `zoosper-page`.

## Dependencies

- PHP 8.5 or newer.
- `zoosper/core` for request, container, and module discovery contracts.
- `zoosper/site` for Site models and active Site resolution.

The package must not depend on concrete content-feature packages such as `zoosper/page`.

## Testing

Run the complete repository suite from the project root:

```bash
zcomposer test
```

Run the focused SEO tests:

```bash
php8.5 vendor/bin/pest app/zoosper-seo/tests
```

Run the standard quality gate:

```bash
php8.5 tools/gate.php
```

## Operational notes

Only absolute HTTP or HTTPS URLs are emitted. Request host headers are not used to manufacture canonical or sitemap URLs. Sitemap and robots responses use explicit content types and public cache headers. When the active Site has no validated absolute base URL, robots output omits the Sitemap declaration rather than guessing a host.

After deployment, verify `/sitemap.xml`, `/robots.txt`, published Page metadata, preview `noindex,nofollow`, and a fresh compiled module manifest. Public machine-readable endpoints currently inherit application session bootstrap behaviour; stateless endpoint handling remains a separate follow-up.
