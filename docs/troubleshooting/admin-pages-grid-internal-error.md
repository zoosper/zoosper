# Admin Pages Grid Internal Error

## Symptom

`/admin/pages` prints escaped toolbar HTML and then a JSON internal error.

## Most likely cause

`TemplateRenderer::partial()` prefixes partial names with `partials/`. The pages grid view calls:

```php
$partial('components/grid/page-filters.php', ...)
$partial('components/grid/pagination.php', ...)
```

That resolves to:

```text
themes/admin/default/templates/partials/components/grid/page-filters.php
themes/admin/default/templates/partials/components/grid/pagination.php
```

If those files do not exist, the renderer throws while the response is already partially built.

## Fix in this package

This package adds the expected partial paths under:

```text
themes/admin/default/templates/partials/components/grid/
```

## Secondary check

If the toolbar is still displayed as text instead of HTML after this fix, inspect:

```text
themes/admin/default/templates/layout.php
```

The content slot must output raw trusted admin-rendered HTML:

```php
<main class="admin-content"><?= $content ?></main>
```

It should not escape the full page content with `$e($content)`, because module views already escape individual user-controlled values.
