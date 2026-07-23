# Permission Tree UX Plan

## Current state

The role permission UI now renders from view partials, which is the right foundation. The current permission tree is functional but visually basic.

## Target direction

A future phase should implement a hierarchical permission tree with a richer look and feel. A jsTree-style nested HTML approach is a good reference because jsTree can transform nested `<ul>` / `<li>` markup into an interactive tree and supports features such as checkboxes, search, themes, and state handling.

Reference: jsTree HTML data documentation shows that the basic HTML data source is a container with nested `<ul>` and `<li>` nodes, and that initial state can be represented with classes or `data-jstree` attributes.

## Recommended Zoosper approach

Do not immediately introduce a hard dependency on jQuery/jsTree into the core admin area.

Instead, implement a progressive enhancement path:

1. Render semantic nested HTML in the permission-tree partial.
2. Add Zoosper-owned CSS for a basic collapsible tree style.
3. Add a small vanilla JS enhancer later for expand/collapse and filter/search.
4. Keep checkboxes as normal form inputs so the form works without JavaScript.
5. Consider optional jsTree integration later as a module-owned admin asset, not as a hard core dependency.

## Proposed phases

### Phase 1.42a — Semantic permission tree markup
Change the permission-tree partial to emit nested list markup grouped by ACL group.

### Phase 1.42b — Admin tree CSS
Add admin CSS classes for collapsible tree layout, spacing, selected states, and readable grouping.

### Phase 1.42c — Progressive enhancement JS
Add optional vanilla JS for expand/collapse all, group toggle, and text filtering.

### Phase 1.42d — Optional jsTree adapter evaluation
Evaluate whether a jsTree adapter is worth supporting as an optional integration. If used, keep it behind module-owned assets and avoid forcing jQuery into the core admin shell.

## Acceptance criteria

- Permission checkboxes remain normal POST fields named `permission_ids[]`.
- No permission assignment behaviour changes.
- UI works without JavaScript.
- JS enhancement does not block form submission.
- Admin assets are cache-versioned consistently.
- The controller remains free of inline markup.
