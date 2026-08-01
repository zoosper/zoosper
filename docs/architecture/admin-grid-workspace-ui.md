# Admin Grid workspace UI

Zoosper's grid interaction model is a reusable workspace rather than a collection
of unrelated buttons. Every participating grid page can expose one compact bar:

- **View** chooses Default view or a named per-admin bookmark;
- **Filters** opens declared text, select and multiselect filters;
- **Columns** controls visibility and order;
- **Save view** stores filters, sorting, page size, visible columns and order;
- **Export CSV** exports the resolved view with the same filters and columns.

ID and Actions are non-toggleable by definition and are always preserved through
hidden form values. Other columns are configurable. Column order supports both
pointer drag and explicit Move up/Move down controls, so drag is not the only
interaction path.

The renderer emits semantic HTML and data attributes without inline JavaScript.
The admin asset layer should progressively enhance panel toggles, drag ordering,
a command-palette style column search, unsaved-change indication and optimistic
feedback. GET remains a complete no-JavaScript path for filters and column state.
Mutating Save/Reset/Delete operations must use authenticated server-side user
identity and CSRF-protected POST routes.

Suggested product behaviour:

1. Default view comes from the grid definition plus the user's current column
   preference.
2. Named views belong to one admin user and one grid key.
3. A view stores filters, sort, page size, visible columns and column order.
4. ID and Actions are locked; all other columns follow each GridColumn's
   `toggleable` property.
5. Export operates on the resolved view, never on arbitrary client-supplied
   repositories or classes.
6. Newly contributed columns appear safely even when an older bookmark omits
   them; retired columns are discarded by normalisation.

This is more modern than reproducing a modal-only Magento interaction: it keeps
one predictable workspace, works without JavaScript, remains keyboard-operable,
and can be progressively enhanced without changing the server contract.
