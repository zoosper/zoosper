# zoosper/pagination

Zoosper-owned pagination contracts and request policy, backed internally by the verified `marko/pagination` 0.8.5 offset paginator.

## Responsibilities

- Composer type: `library`; this package has no runtime bindings and therefore is not a native module.
- Own `Zoosper\Pagination\Pager` and `Zoosper\Pagination\PaginationResult`.
- Normalize page and page-size query values before repositories calculate an offset.
- Enforce the default maximum page size of `100` and maximum page of `100_000`.
- Keep Grid and feature modules independent of Marko implementation classes.

## Architecture

`Pager` preserves Zoosper's public request-normalization policy, including the default page size of `20`. `PaginationResult` preserves the existing immutable public properties and delegates offset pagination calculations to `Marko\Pagination\OffsetPaginator`. Marko classes are private implementation details and must not appear in Grid or feature-module signatures. Cursor pagination remains outside this extraction.

## Dependencies

- `php`: `^8.5`.
- `marko/pagination`: exactly `0.8.5`, the source contract verified for Phase 10BN.
- Development dependencies: Pest 3 and PHPUnit 11.

## Extension points

The stable extension boundary is the pair of Zoosper-owned value objects. Add future serialization or cursor adapters in this package rather than exposing third-party classes to consumers.

## Security and compatibility

Page numbers start at `1`. Invalid query pages normalize safely; page size is bounded independently; page is clamped to `100_000` to limit database OFFSET cost amplification. Existing constructor properties, total-page semantics, and Previous/Next behaviour remain unchanged.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest packages/zoosper-pagination/tests`.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper. This library intentionally has no `module.php`; installing `marko/pagination` adds the discoverable Marko module while Zoosper's compatibility boundary remains a dependency-only library.
