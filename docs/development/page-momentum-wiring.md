# Page Momentum Live Wiring

## Plain-English summary

Phase 1.86 built the code that calculates the dashboard numbers. This phase
connects it to your real database safely, without guessing your column names.

The trick: we first *probe* your `pages` table to learn its real columns, then
feed those names to a schema-adaptive query. If a column is missing, that card
shows 0 instead of breaking the page.

## Step 1 — Probe your schema

```bash
php8.5 tools/probe-page-momentum-schema.php --database=/path/to/your.sqlite
# or MySQL:
php8.5 tools/probe-page-momentum-schema.php \
  --database="mysql:host=localhost;dbname=zoosper" --user=USER --password=PASS
# custom table name:
php8.5 tools/probe-page-momentum-schema.php --database=... --table=cms_pages
```

It prints a ready-to-paste column map and writes
`docs/reports/page-momentum-schema-probe.json`.

## Step 2 — Bind in the admin module services.php

```php
use PDO;
use Zoosper\Admin\PageMomentum\PageMomentumColumnMap;
use Zoosper\Admin\PageMomentum\PageMomentumFactsProvider;
use Zoosper\Admin\PageMomentum\PageMomentumQueryInterface;
use Zoosper\Admin\PageMomentum\SchemaAdaptivePageMomentumQuery;

PageMomentumQueryInterface::class => static function ($c): PageMomentumQueryInterface {
    return new SchemaAdaptivePageMomentumQuery(
        $c->get(PDO::class),
        PageMomentumColumnMap::fromArray([
            // paste the probe's suggested map here
            'table' => 'pages',
            'status' => 'status',
            'title' => 'title',
            'published_at' => 'published_at',
            'updated_at' => 'updated_at',
            'published_value' => 'published',
        ]),
    );
},

PageMomentumFactsProvider::class => static fn ($c) =>
    new PageMomentumFactsProvider($c->get(PageMomentumQueryInterface::class)),
```

## Step 3 — Use it in the page-momentum controller

```php
public function __construct(
    private readonly PageMomentumFactsProvider $facts,
) {}

public function index(): Response
{
    $facts = $this->facts->facts();

    return $this->view('admin/page-momentum/index', [
        'cards' => $facts->toArray(),
    ]);
}
```

Then in the view, replace placeholder card values with `$cards['total_pages']`,
`$cards['published_pages']`, `$cards['published_share_percent']`, etc.

## Why this is safe

- Read-only: no writes anywhere in this path.
- Schema-adaptive: unknown/missing columns degrade to 0/null, never a SQL error.
- Identifier-guarded: column names are validated to prevent injection.
- Fully tested: custom-column and missing-column cases are covered by Pest.
