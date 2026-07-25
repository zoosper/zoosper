# Page Momentum Cards — Live Wiring

Your schema probe confirmed the `pages` table uses the default column names, so
wiring is the simplest possible path.

## Step 1 — Confirm the published status value

```sql
SELECT status, COUNT(*) FROM pages GROUP BY status;
```

If published rows use `status = 'published'`, no change is needed. Otherwise pass
`'published_value'` in the column map (Step 2).

## Step 2 — DI binding (admin module services.php)

```php
use PDO;
use Zoosper\Admin\PageMomentum\PageMomentumColumnMap;
use Zoosper\Admin\PageMomentum\PageMomentumFactsProvider;
use Zoosper\Admin\PageMomentum\PageMomentumQueryInterface;
use Zoosper\Admin\PageMomentum\SchemaAdaptivePageMomentumQuery;

PageMomentumQueryInterface::class => static function ($c): PageMomentumQueryInterface {
    return new SchemaAdaptivePageMomentumQuery(
        $c->get(PDO::class),
        new PageMomentumColumnMap(), // defaults match your pages table
        // If published uses a different value:
        // PageMomentumColumnMap::fromArray(['published_value' => 'active']),
    );
},

PageMomentumFactsProvider::class => static fn ($c) =>
    new PageMomentumFactsProvider($c->get(PageMomentumQueryInterface::class)),
```

## Step 3 — Controller

```php
use Zoosper\Admin\PageMomentum\PageMomentumCardsPresenter;
use Zoosper\Admin\PageMomentum\PageMomentumFactsProvider;

public function __construct(
    private readonly PageMomentumFactsProvider $facts,
) {}

public function index(): Response
{
    $cards = (new PageMomentumCardsPresenter($this->facts->facts()))->cards();

    return $this->view('admin/page-momentum/cards', ['cards' => $cards]);
}
```

## Step 4 — View

The partial `admin/page-momentum/cards.php` renders the cards and escapes all
output. Drop it into your existing page-momentum layout, or include it:

```php
<?php include __DIR__ . '/cards.php'; ?>
```

## What you'll see

Six live cards: Total pages, Published (with % share), Drafts, Published (7 days),
Updated (7 days), and Most recent update (title + timestamp).

## Safety

- Read-only throughout; no writes on this path.
- Schema-adaptive: a missing column degrades to 0/null instead of erroring.
- Output escaped in the view.
