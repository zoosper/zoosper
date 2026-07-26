# Admin interface

The admin area is composed from module routes, Latte views, form metadata, grids, and shared layout assets.

## Controllers and views

Controllers should stay thin:

1. Authorisation is enforced on the route (middleware) with optional defense-in-depth in the controller.
2. Read the request; call services or the entity save lifecycle.
3. Return `Response` via `AdminViewRenderer` and Latte templates.

HTML belongs in `.latte` templates under module `resources/views/`, not large heredocs in PHP. Latte auto-escapes output; only intentionally sanitised CMS body HTML uses explicit no-escape slots.

Reusable fragments (notices, status controls, role pickers) should be partials/components in templates so themes can override presentation without forking controllers.

## Admin forms (sections & processors)

Modules contribute form structure through `config/admin_forms.php`, aggregated from:

```text
app/*/config/admin_forms.php
modules/*/config/admin_forms.php
config/admin_forms.php
```

This registers sections and save processors for entity keys such as `page.form` without editing core controllers.

## Field injection (`admin_ui.php`)

Magento-inspired metadata lets modules adjust existing forms:

| Operation | Effect |
|-----------|--------|
| `fields` | Define normal fields |
| `remove` | Drop a field |
| `replace` | Replace a field definition |
| `inject` | Insert fields relative to anchors (`after.slug`, etc.) |

Example:

```php
return [
    'admin.pages.form' => [
        'remove' => ['meta_title'],
        'replace' => [
            'content' => ['type' => 'textarea', 'label' => 'Page Body', 'rows' => 18],
        ],
        'inject' => [
            'after.slug' => [
                'seo_score' => ['type' => 'readonly', 'label' => 'SEO Score'],
            ],
        ],
    ],
];
```

## Grids (pagination, search, filters)

Shared pagination types (`Pager`, `PaginationResult`) back admin listings. The pages grid at `/admin/pages` supports:

```text
q          search
status     filter
site_id    filter
page       page number
page_size  page size
```

Example: `/admin/pages?page=2&page_size=20&q=home&status=published`

Other entity grids follow the same pattern as modules add them.

## Flash messages & translations

Prefer translation-ready messages:

```php
$this->flashMessages?->error($this->t('Unable to save page.'));
```

Avoid hard-coded English at call sites so locale-aware translators can replace `IdentityTranslator` later.

Admin notices use canonical CSS selectors (`.notice-success`, `.notice-error`, etc.).

## Admin assets

Modules register CSS/JS through `config/admin_assets.php`. Static files are served from configured asset paths under `/assets/...`, not mixed with application routes.

## Editor.js (admin)

When enabled via env, the admin page editor stores:

- `content` — sanitised HTML fallback
- `content_json` — validated Editor.js document
- `content_format` — `html` or `block_json` for rendering mode

See [Sites, pages & content](sites-pages-and-content.md).

## Related guides

- [Routing, middleware & access control](routing-middleware-and-access-control.md)
- [Entity save lifecycle](entity-save-lifecycle.md)
- [Themes & templates](themes-and-templates.md)
