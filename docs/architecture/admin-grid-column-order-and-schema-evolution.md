# Admin Grid column order and schema evolution

## Column ordering

The Columns workspace supports pointer drag and Move up/Move down controls. ID and
Actions are mandatory boundary columns. Configurable columns may be reordered between
them; ID remains first and Actions remains last.

## New data versus new Grid columns

Adding a database column does not automatically expose it in the admin Grid. A feature
module must deliberately add a `GridColumn` through its Grid definition or a
`config/grid_columns.php` contribution, and its read projection must provide the row
value. This prevents accidental exposure of password, token, 2FA or internal fields.

## Existing bookmarks

An older saved column order is reconciled against the live definition: retired and
unknown keys are removed, and newly declared Grid columns are appended to the known
order. Existing bookmark visibility remains stable, so a newly introduced optional
column is initially available in Columns but is not forced visible in an old saved
view. New/default views use the column's `defaultVisible` policy.

If a product change requires a new column to appear in every historical bookmark,
that must be an explicit bookmark-state migration rather than an implicit database
schema side effect.
