# Admin grid column customisation

## Purpose

Zoosper admin grids support configurable column visibility and ordering while keeping the server-rendered grid and the browser workspace aligned.

## Ownership

`zoosper-admin-grid` owns the reusable grid model and generic column-order behaviour. `zoosper-admin` owns admin-shell integration and the currently rendered bridge asset. The bridge is temporary integration glue and must not become a second independent grid framework.

The browser must receive one effective drag/reflection runtime. Duplicate standalone observers are not allowed.

## Column identity contract

Column identity is key-based, never numeric-index based.

The Columns panel uses:

```html
<label data-column-key="title">
    <input type="hidden" name="column_order[]" value="title">
</label>
```

Every table header and body cell must use the matching key:

```html
<th data-grid-column="title">Title</th>
<td data-grid-column="title">Example</td>
```

`id` is the permanently locked first column. `actions` is the permanently locked last column. All rendered headers, including `site_name` and `actions`, should carry explicit `data-grid-column` attributes. Runtime positional repair is compatibility-only and must not be treated as the durable server contract.

## Runtime behaviour

The loaded drag bridge:

- enables native drag behaviour only for movable columns;
- updates `column_order[]` after a successful reorder;
- reflects the new order immediately across the table header and every body row;
- marks the current view as having unsaved changes;
- does not silently persist the bookmark;
- leaves Apply columns and Save view as the explicit persistence actions.

The runtime must use column keys and must not couple behaviour to `cellIndex` or hard-coded numeric positions.

## Asset delivery

Admin grid assets are resolved through the module asset route. Source files remain outside the public webroot. The committed application/package source must be sufficient to reproduce the runtime after a clean Composer install. Manual edits under `vendor/` are diagnostic only and are never part of the durable implementation.

The final asset pipeline should use content-derived cache busting. Until that is implemented, browser hard refreshes may be required after JavaScript or CSS changes.

## Persistence and new columns

Saved bookmarks store user choices, not a frozen replacement for the server definition. The server-defined column registry remains authoritative. Newly contributed columns must be merged into an existing bookmark according to their declared default visibility and position while preserving recognised user ordering.

Unknown or removed bookmark keys must be ignored safely.

## Accessibility

Locked columns must not advertise draggable behaviour. A future accessibility pass should add keyboard reordering, focus preservation and an announcement of the resulting position.

## Verification contract

Coverage must include:

1. JavaScript syntax validation for the exact asset rendered by the admin layout.
2. One rendered asset URL, resolving to the validated source.
3. ID first and Actions last under every reorder attempt.
4. Header and all body rows moving together.
5. Hidden order inputs matching the visible order.
6. Dirty state changing only when order differs from the saved baseline.
7. Bookmark reload reproducing the saved server-rendered order.
8. New contributed columns merging safely into older bookmarks.

Source-string assertions may supplement these checks but must not replace behavioural coverage.

## Known follow-up work

- Move the generic runtime fully into `zoosper-admin-grid` and reduce the application bridge to registration only.
- Remove compatibility-time header-key inference after all grid renderers emit explicit keys.
- Add content-hashed asset URLs.
- Add browser/DOM behavioural coverage.
- Roll the unified workspace out to remaining admin grids only after the Pages implementation is consolidated.


## Phase 4ZH runtime consolidation

The package-owned `grid-compact-column-order.js` is now the canonical source for drag, keyboard movement, hidden-order synchronisation and immediate table reflection. The application bridge is an intentionally identical compatibility copy while the current admin asset route remains application-owned. A parity test prevents the two physical files from drifting.

The browser-facing compatibility bridge now uses a SHA-256-derived twelve-character asset version. Editing the runtime or stylesheet therefore requires updating the manifest version to the corresponding content digest; tests enforce this relationship.

The runtime is one idempotently-bound controller rather than separate drag and reflection observers. A successful movement performs one ordered transaction: synchronise hidden order inputs, reorder keyed table cells, mark the view dirty, then publish `zoosper:grid:columns-reordered`.

Header-key inference remains a compatibility boundary for current server output. Removing it still requires every grid renderer to emit explicit `data-grid-column` attributes, including `site_name` and `actions`.


## Phase 4ZI explicit header identity

Every header branch in `GridHtmlRenderer` now emits an escaped `data-grid-column` key. The browser runtime no longer guesses missing header identities from numeric position. Table reflection is therefore fully key-driven for sortable and non-sortable columns, including `site_name` and `actions`.

The compatibility application bridge remains byte-identical to the package runtime, and its content-derived version is refreshed whenever the runtime changes.
