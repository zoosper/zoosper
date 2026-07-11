# Phase 0.78 - Frontend Sanitised HTML Rendering

Zoosper stores CMS page body as sanitised HTML while the block_json storage transition is in progress.

Frontend templates must therefore render `$content` as trusted sanitised HTML, not as escaped plain text.

## Rule

```text
Admin/input/save path: sanitise HTML before persistence.
Frontend render path: render persisted sanitised HTML as HTML.
```

For Latte templates use:

```latte
{$content|noescape}
```

For PHP templates use:

```php
<?= $content ?? '' ?>
```

Do not render page body with `$e($content)` or `htmlspecialchars($content)`, otherwise users see raw tags like `&lt;h2&gt;`.
