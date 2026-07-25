# Page Momentum Facts (Read-Only)

## Plain-English summary

The page momentum dashboard shows a few simple cards that answer: "how is our
content doing right now?" This phase adds the code that produces those numbers
from real data, without changing how pages are rendered or saved.

It is deliberately **read-only** — it only counts and reads; it never writes.

## The cards

| Card | Meaning |
| --- | --- |
| Total pages | How many pages exist. |
| Published pages | Pages that are live. |
| Draft pages | Pages not yet published. |
| Published (7 days) | Pages published in the last week. |
| Updated (7 days) | Pages edited in the last week. |
| Most recent update | The page changed most recently, with its time. |
| Published share % | What portion of pages are live. |

## The pieces

- `PageMomentumQueryInterface` — the tiny read-only contract (counts + recent activity).
- `PageMomentumFacts` — an immutable value object holding the computed numbers.
- `PageMomentumFactsProvider` — turns the query results into facts. Pure and testable.
- `SqlitePageMomentumQuery` — a PDO-backed adapter that works on SQLite and MySQL.

## Why a contract instead of direct database access?

Keeping a small interface between the provider and the database means:

- the provider logic can be unit-tested with an in-memory SQLite fixture;
- you can later swap in your own repository-backed implementation without changing
  the provider or the dashboard;
- nothing here can accidentally reach into the page render hot path.

## Wiring it up (follow-up phase)

1. In the admin module's `services.php`, bind the contract to an implementation:

   ```php
   Zoosper\Admin\PageMomentum\PageMomentumQueryInterface::class =>
       fn ($c) => new Zoosper\Admin\PageMomentum\SqlitePageMomentumQuery($c->get(PDO::class)),
   ```

2. Inject `PageMomentumFactsProvider` into the page-momentum controller.

3. In the controller action, call `$provider->facts()` and pass `$facts->toArray()`
   to the view, replacing any placeholder card values.

## Testing

```bash
vendor/bin/pest --filter=PageMomentumFactsProvider
```

The test seeds a deterministic `pages` fixture and asserts every card value,
including the 7-day windows and the empty-table edge case.
