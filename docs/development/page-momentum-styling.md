# Page Momentum Styling & Rendering

## Confirmed configuration

Your `pages` table uses `status = 'published'` (3 published at time of wiring),
which matches the defaults. No column-map override is required.

## Include the stylesheet

Add to your admin layout `<head>`:

```html
<link rel="stylesheet" href="/assets/css/page-momentum.css">
```

Or copy `resources/assets/css/page-momentum.css` into your existing admin asset
path / build pipeline. The CSS is self-contained: a responsive card grid, hover
lift, an accent on the "most recent" card, and a dark-mode variant.

## Rendering options

### Option A — your template engine (preferred)
Pass the presenter output to your view as in Phase 1.88:

```php
$cards = (new PageMomentumCardsPresenter($this->facts->facts()))->cards();
return $this->view('admin/page-momentum/cards', ['cards' => $cards]);
```

### Option B — the renderer helper (no engine needed)
Useful in a controller that returns a raw HTML fragment, or in tests:

```php
use Zoosper\Admin\PageMomentum\PageMomentumCardsRenderer;

$html = (new PageMomentumCardsRenderer())->render($this->facts->facts());
```

## The render smoke test

`PageMomentumCardsRenderTest` includes the real partial and asserts:

- the expected numbers/labels appear in the HTML;
- the "most recent" card is accented;
- output is HTML-escaped (an injected `<script>` is neutralised);
- the empty state renders when there are no cards.

This complements the logic tests by proving the actual view file runs correctly.

Run just this test:

```bash
vendor/bin/pest --filter=PageMomentumCardsRender
```
