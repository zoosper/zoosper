# Sites, pages & content

Zoosper is multi-site: each request carries a **site context**, pages belong to a `site_id`, and only **published** pages render on the public site.

## Request flow (frontend)

```text
HTTP request
  -> Request::fromGlobals()
  -> Site context resolved once -> Request::withSiteContext()
  -> Router (no match)
  -> PageController
  -> PageRepository (site_id + slug)
  -> PageRenderer + theme
  -> HTML response
```

Homepage uses the site’s `homepage_slug` when the path is `/`.

## Sites & domains (admin)

Sites and site domains are managed in admin (launch-readiness CRUD):

```text
/admin/sites
/admin/site-domains
```

Site fields include code, name, status, default locale, and theme code. Domains map hosts (and optional path prefix) to a site with primary-domain semantics.

Non-goals for this layer: multi-database tenancy, DNS verification, Magento-style website/store/store-view hierarchy.

## Page content model

| Column / concept | Role |
|------------------|------|
| `content` | Sanitised HTML body (legacy and fallback) |
| `content_json` | Editor.js block document |
| `content_format` | `html` or `block_json` |

On save, HTML is sanitised; JSON passes `BlockJsonValidator`.

## Public rendering

- **`content_format = html`** (or bridge saves): render `pages.content` through the page renderer with appropriate sanitisation boundaries.
- **`content_format = block_json`**: render supported blocks via `BlockJsonToHtmlRenderer` when JSON is valid; fall back to `content` if JSON is missing, invalid, or empty.

Supported block types include paragraph, headers (h2–h4), and ordered/unordered lists; media/image blocks integrate as Editor.js and media modules mature.

Titles and slugs are escaped in templates. Body HTML is prepared by the renderer so templates do not double-escape.

## SEO & metadata

Page modules expose SEO fields (title, meta description, etc.) through admin forms; exact fields depend on `zoosper-page` form providers.

## Content API

```http
GET /api/v1/content/page?slug=home
```

Returns JSON for headless or integration use (public flag on route as configured).

## HTML sanitisation

Rich HTML uses HTMLPurifier via `HtmlSanitizerFactory`. The sanitiser is for CMS body content only — never pass secrets or auth tokens through it.

See [Security foundations](security-foundations.md).

## Related guides

- [Admin interface](admin-interface.md)
- [Themes & templates](themes-and-templates.md)
- [Media library](media-library.md)
- [Events & observers](events-and-observers.md) — publish/unpublish hooks
